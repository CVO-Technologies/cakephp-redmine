<?php

namespace CvoTechnologies\Redmine\Test\TestCase\Model;

use Cake\Cache\Cache;
use Cake\Network\Http\Response;
use Cake\TestSuite\TestCase;
use CvoTechnologies\Redmine\Model\Endpoint;
use CvoTechnologies\Redmine\Schema;
use Muffin\Webservice\Connection;

class EndpointTest extends TestCase
{

    /**
     * @var Endpoint
     */
    public $endpoint;

    public function setUp()
    {
        parent::setUp();

        $this->endpoint = new Endpoint([
            'connection' => new Connection([
                'name' => 'redmine',
                'driver' => 'CvoTechnologies\Redmine\Webservice\Driver\Redmine'
            ]),
            'endpoint' => 'issues'
        ]);

        Cache::clear();
    }

    public function testSetBaseSchema()
    {
        $schema = new Schema('issues');

        $this->assertEquals($this->endpoint, $this->endpoint->baseSchema($schema));

        $this->assertEquals($schema, $this->endpoint->baseSchema());
    }

    public function testCreateBaseSchema()
    {
        $this->assertInstanceOf('\CvoTechnologies\Redmine\Schema', $this->endpoint->baseSchema());
        $this->assertEquals('issues', $this->endpoint->baseSchema()->name());
    }

    public function testSchemaCreation()
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

        $schema = $this->endpoint->schema();

        $this->assertInstanceOf('\CvoTechnologies\Redmine\Schema', $schema);
        $this->assertEquals('issues', $schema->name());
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
