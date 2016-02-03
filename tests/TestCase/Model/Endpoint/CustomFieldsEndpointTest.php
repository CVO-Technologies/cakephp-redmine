<?php

namespace CvoTechnologies\Redmine\Test\TestCase\Model\Endpoint;

use Cake\Cache\Cache;
use Cake\Network\Http\Response;
use Cake\TestSuite\TestCase;
use CvoTechnologies\Redmine\Model\Endpoint\CustomFieldsEndpoint;
use CvoTechnologies\Redmine\Schema;
use Muffin\Webservice\Connection;

class CustomFieldsEndpointTest extends TestCase
{

    /**
     * @var \CvoTechnologies\Redmine\Model\Endpoint\CustomFieldsEndpoint
     */
    public $endpoint;

    public function setUp()
    {
        parent::setUp();

        $this->endpoint = new CustomFieldsEndpoint([
            'connection' => new Connection([
                'name' => 'redmine',
                'driver' => 'CvoTechnologies\Redmine\Webservice\Driver\Redmine'
            ]),
            'endpoint' => 'custom_fields',
        ]);

        Cache::clear();
    }

    public function testAlterSchema()
    {
        $this->endpoint->webservice()->driver()->client($this->_clientMock('get', '/custom_fields.json', [], [
            'custom_fields' => [
                [
                    'id' => 1,
                    'name' => 'Some boolean',
                    'customized_type' => 'issue',
                    'field_format' => 'bool',
                    'regexp' => '',
                    'default_value' => '',
                    'visible' => true
                ],
                [
                    'id' => 2,
                    'name' => 'Some integer',
                    'customized_type' => 'user',
                    'field_format' => 'int',
                    'regexp' => '',
                    'default_value' => '',
                    'visible' => true
                ]
            ]
        ]));

        $schema = $this->endpoint->alterSchema(new Schema('issues'));

        $this->assertInstanceOf('\CvoTechnologies\Redmine\Schema', $schema);
        $this->assertEquals([
            'type' => 'bool',
            'default' => '',
            'custom_field_id' => 1,
            'custom_field_filterable' => null,
            'length' => null,
            'precision' => null,
            'null' => null,
            'comment' => null
        ], $schema->column('some_boolean'));
    }

    protected function _clientMock($method, $url, $parameters, $response)
    {
        $client = $this->getMockBuilder('Cake\Network\Http\Client')
            ->disableOriginalConstructor()
            ->setMethods([$method])
            ->getMock();
        $client
            ->expects($this->once())
            ->method($method)
            ->with($url, $parameters)
            ->willReturn(new Response([
                'HTTP/1.1 200 OK'
            ], json_encode($response)));

        return $client;
    }

    public function tearDown()
    {
        parent::tearDown();

        Cache::clear();
    }
}
