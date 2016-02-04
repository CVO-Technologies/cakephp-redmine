<?php

namespace CvoTechnologies\Redmine\Test\TestCase\Webservice;

use Cake\Network\Http\Response;
use Cake\TestSuite\TestCase;
use CvoTechnologies\Redmine\Model\Endpoint;
use CvoTechnologies\Redmine\Schema;
use CvoTechnologies\Redmine\Webservice\Driver\Redmine;
use CvoTechnologies\Redmine\Webservice\RedmineWebservice;
use Muffin\Webservice\Connection;
use Muffin\Webservice\Query;

class RedmineWebserviceTest extends TestCase
{

    /**
     * @var Redmine
     */
    public $driver;
    /**
     * @var RedmineWebservice
     */
    public $webservice;

    public function setUp()
    {
        parent::setUp();

        $this->driver = new Redmine([]);
        $this->webservice = new RedmineWebservice([
            'driver' => $this->driver,
            'endpoint' => 'issues',
        ]);
    }

    public function testReadKey()
    {
        $this->webservice->driver()->config('api_key', '1234');
        $this->webservice->driver()->client($this->_clientMock('get', '/issues.json', [
            'key' => '1234'
        ], [
            'issues' => []
        ]));
        $query = new Query($this->webservice, new Endpoint([
            'connection' => new Connection([
                'name' => 'redmine',
                'driver' => 'CvoTechnologies\Redmine\Webservice\Driver\Redmine'
            ]),
            'endpoint' => 'issues',
            'schema' => new Schema('issues')
        ]));
        $query->read();

        $resultSet = $this->webservice->execute($query);

        $this->assertEquals(0, $resultSet->total());
        $this->assertInstanceOf('Muffin\Webservice\ResultSet', $resultSet);
    }

    public function testReadOverall()
    {
        $this->webservice->driver()->client($this->_clientMock('get', '/issues.json', [], [
            'issues' => [],
            'total_count' => 0
        ]));
        $query = new Query($this->webservice, new Endpoint([
            'connection' => new Connection([
                'name' => 'redmine',
                'driver' => 'CvoTechnologies\Redmine\Webservice\Driver\Redmine'
            ]),
            'endpoint' => 'issues',
            'schema' => new Schema('issues')
        ]));
        $query->read();

        $resultSet = $this->webservice->execute($query);

        $this->assertEquals(0, $resultSet->total());
        $this->assertInstanceOf('Muffin\Webservice\ResultSet', $resultSet);
    }

    public function testGetSingleResource()
    {
        $this->webservice->driver()->client($this->_clientMock('get', '/issues/1.json', [], [
            'issue' => [
                'subject' => 'Hello'
            ]
        ]));
        $query = new Query($this->webservice, new Endpoint([
            'connection' => new Connection([
                'name' => 'redmine',
                'driver' => 'CvoTechnologies\Redmine\Webservice\Driver\Redmine'
            ]),
            'endpoint' => 'issues',
            'schema' => new Schema('issues')
        ]));
        $query->read();
        $query->where([
            'id' => 1
        ]);

        $resultSet = $this->webservice->execute($query);

        $this->assertInstanceOf('Muffin\Webservice\ResultSet', $resultSet);
        $this->assertEquals(1, $resultSet->total());
        $this->assertInstanceOf('Muffin\Webservice\Model\Resource', $resultSet->first());
        $this->assertEquals('Hello', $resultSet->first()->subject);
    }

    public function testGetSingleResourceJournals()
    {
        $this->webservice->driver()->client($this->_clientMock('get', '/issues/1.json', [
            'include' => 'journals,changesets'
        ], [
            'issue' => [
                'subject' => 'Hello'
            ]
        ]));
        $query = new Query($this->webservice, new Endpoint([
            'connection' => new Connection([
                    'name' => 'redmine',
                    'driver' => 'CvoTechnologies\Redmine\Webservice\Driver\Redmine'
                ]),
            'endpoint' => 'issues',
            'schema' => new Schema('issues')
        ]));
        $query->read();
        $query->where([
            'id' => 1
        ]);
        $query->applyOptions([
            'include' => [
                'journals',
                'changesets'
            ]
        ]);

        $resultSet = $this->webservice->execute($query);

        $this->assertInstanceOf('Muffin\Webservice\ResultSet', $resultSet);
        $this->assertEquals(1, $resultSet->total());
        $this->assertInstanceOf('Muffin\Webservice\Model\Resource', $resultSet->first());
        $this->assertEquals('Hello', $resultSet->first()->subject);
    }

    public function testGetSingleResourceJournalSingle()
    {
        $this->webservice->driver()->client($this->_clientMock('get', '/issues/1.json', [
            'include' => 'journals'
        ], [
            'issue' => [
                'subject' => 'Hello'
            ]
        ]));
        $query = new Query($this->webservice, new Endpoint([
            'connection' => new Connection([
                'name' => 'redmine',
                'driver' => 'CvoTechnologies\Redmine\Webservice\Driver\Redmine'
            ]),
            'endpoint' => 'issues',
            'schema' => new Schema('issues')
        ]));
        $query->read();
        $query->where([
            'id' => 1
        ]);
        $query->applyOptions([
            'include' => 'journals'
        ]);

        $resultSet = $this->webservice->execute($query);

        $this->assertInstanceOf('Muffin\Webservice\ResultSet', $resultSet);
        $this->assertEquals(1, $resultSet->total());
        $this->assertInstanceOf('Muffin\Webservice\Model\Resource', $resultSet->first());
        $this->assertEquals('Hello', $resultSet->first()->subject);
    }

    public function testResourceSearch()
    {
        $this->webservice->driver()->client($this->_clientMock('get', '/issues.json', [
            'status' => 'open'
        ], [
            'issues' => []
        ]));
        $query = new Query($this->webservice, new Endpoint([
            'connection' => new Connection([
                'name' => 'redmine',
                'driver' => 'CvoTechnologies\Redmine\Webservice\Driver\Redmine'
            ]),
            'endpoint' => 'issues',
            'schema' => new Schema('issues')
        ]));
        $query->read();
        $query->where([
            'status' => 'open'
        ]);

        $resultSet = $this->webservice->execute($query);

        $this->assertEquals(0, $resultSet->total());
        $this->assertInstanceOf('Muffin\Webservice\ResultSet', $resultSet);
    }

    public function testResourceSort()
    {
        $this->webservice->driver()->client($this->_clientMock('get', '/issues.json', [
            'sort' => 'category:desc'
        ], [
            'issues' => []
        ]));
        $query = new Query($this->webservice, new Endpoint([
            'connection' => new Connection([
                    'name' => 'redmine',
                    'driver' => 'CvoTechnologies\Redmine\Webservice\Driver\Redmine'
                ]),
            'endpoint' => 'issues',
            'schema' => new Schema('issues')
        ]));
        $query->read();
        $query->order([
            'category' => 'DESC'
        ]);

        $resultSet = $this->webservice->execute($query);

        $this->assertEquals(0, $resultSet->total());
        $this->assertInstanceOf('Muffin\Webservice\ResultSet', $resultSet);
    }

    public function testResourceSortMultiple()
    {
        $this->webservice->driver()->client($this->_clientMock('get', '/issues.json', [
            'sort' => 'category:desc,project_id,subject:asc'
        ], [
            'issues' => []
        ]));
        $query = new Query($this->webservice, new Endpoint([
            'connection' => new Connection([
                    'name' => 'redmine',
                    'driver' => 'CvoTechnologies\Redmine\Webservice\Driver\Redmine'
                ]),
            'endpoint' => 'issues',
            'schema' => new Schema('issues')
        ]));
        $query->read();
        $query->order([
            'category' => 'DESC',
            'project_id',
            'subject' => 'ASC',
        ]);

        $resultSet = $this->webservice->execute($query);

        $this->assertEquals(0, $resultSet->total());
        $this->assertInstanceOf('Muffin\Webservice\ResultSet', $resultSet);
    }

    public function testResourceLimit()
    {
        $this->webservice->driver()->client($this->_clientMock('get', '/issues.json', [
            'limit' => 10
        ], [
            'issues' => []
        ]));
        $query = new Query($this->webservice, new Endpoint([
            'connection' => new Connection([
                    'name' => 'redmine',
                    'driver' => 'CvoTechnologies\Redmine\Webservice\Driver\Redmine'
                ]),
            'endpoint' => 'issues',
            'schema' => new Schema('issues')
        ]));
        $query->read();
        $query->limit(10);

        $resultSet = $this->webservice->execute($query);

        $this->assertEquals(0, $resultSet->total());
        $this->assertInstanceOf('Muffin\Webservice\ResultSet', $resultSet);
    }

    public function testResourceOffset()
    {
        $this->webservice->driver()->client($this->_clientMock('get', '/issues.json', [
            'offset' => 10
        ], [
            'issues' => []
        ]));
        $query = new Query($this->webservice, new Endpoint([
            'connection' => new Connection([
                    'name' => 'redmine',
                    'driver' => 'CvoTechnologies\Redmine\Webservice\Driver\Redmine'
                ]),
            'endpoint' => 'issues',
            'schema' => new Schema('issues')
        ]));
        $query->read();
        $query->offset(10);

        $resultSet = $this->webservice->execute($query);

        $this->assertEquals(0, $resultSet->total());
        $this->assertInstanceOf('Muffin\Webservice\ResultSet', $resultSet);
    }

    public function testCustomFieldSearch()
    {
        $this->webservice->driver()->client($this->_clientMock('get', '/issues.json', [
            'cf_1' => true
        ], [
            'issues' => []
        ]));
        $query = new Query($this->webservice, new Endpoint([
            'connection' => new Connection([
                'name' => 'redmine',
                'driver' => 'CvoTechnologies\Redmine\Webservice\Driver\Redmine'
            ]),
            'endpoint' => 'issues',
            'schema' => new Schema('issues', [
                'custom_bool_field' => [
                    'type' => 'boolean',
                    'custom_field_id' => 1,
                    'custom_field_filterable' => true
                ]
            ])
        ]));
        $query->read();
        $query->where([
            'custom_bool_field' => true
        ]);

        $resultSet = $this->webservice->execute($query);

        $this->assertEquals(0, $resultSet->total());
        $this->assertInstanceOf('Muffin\Webservice\ResultSet', $resultSet);
    }

    public function testCustomFieldResult()
    {
        $this->webservice->driver()->client($this->_clientMock('get', '/issues.json', [], [
            'issues' => [
                [
                    'subject' => 'Hello',
                    'custom_fields' => [
                        [
                            'id' => 1,
                            'name' => 'Custom bool field',
                            'value' => false
                        ],
                        [
                            'id' => 2,
                            'name' => 'Custom int field',
                        ],
                        [
                            'id' => 3,
                            'name' => 'Custom link field',
                            'value' => 'https://cvo-technologies.com/'
                        ],
                        [
                            'id' => 4,
                            'name' => 'Extra int field',
                            'value' => '10'
                        ]
                    ]
                ]
            ]
        ]));
        $query = new Query($this->webservice, new Endpoint([
            'connection' => new Connection([
                'name' => 'redmine',
                'driver' => 'CvoTechnologies\Redmine\Webservice\Driver\Redmine'
            ]),
            'endpoint' => 'issues',
            'schema' => new Schema('issues', [
                'subject' => [
                    'type' => 'string'
                ],
                'custom_bool_field' => [
                    'type' => 'bool',
                    'custom_field_id' => 1,
                    'custom_field_filterable' => true
                ],
                'custom_int_field' => [
                    'type' => 'int',
                    'custom_field_id' => 2,
                    'custom_field_filterable' => true
                ],
                'custom_link_field' => [
                    'type' => 'link',
                    'custom_field_id' => 3,
                    'custom_field_filterable' => true
                ],
                'extra_int_field' => [
                    'type' => 'int',
                    'custom_field_id' => 4,
                    'custom_field_filterable' => true
                ],
            ])
        ]));
        $query->read();

        $resultSet = $this->webservice->execute($query);

        $this->assertInstanceOf('Muffin\Webservice\ResultSet', $resultSet);
        $this->assertEquals(false, $resultSet->first()->custom_bool_field);
        $this->assertEquals(null, $resultSet->first()->custom_int_field);
        $this->assertEquals('https://cvo-technologies.com/', $resultSet->first()->custom_link_field);
        $this->assertEquals(10, $resultSet->first()->extra_int_field);
    }

    public function testReadSwitchUser()
    {
        $this->webservice->driver()->client($this->_clientMock('get', '/issues.json', [], [
            'issues' => [
                [
                    'subject' => 'Hello'
                ]
            ]
        ], [
            'X-Redmine-Switch-User: marlinc'
        ]));
        $query = new Query($this->webservice, new Endpoint([
            'connection' => new Connection([
                'name' => 'redmine',
                'driver' => 'CvoTechnologies\Redmine\Webservice\Driver\Redmine'
            ]),
            'endpoint' => 'issues',
            'schema' => new Schema('issues')
        ]));
        $query->read();
        $query->applyOptions([
            'user' => 'marlinc'
        ]);

        $resultSet = $this->webservice->execute($query);

        $this->assertInstanceOf('Muffin\Webservice\ResultSet', $resultSet);
        $this->assertEquals(1, $resultSet->total());
        $this->assertInstanceOf('Muffin\Webservice\Model\Resource', $resultSet->first());
        $this->assertEquals('Hello', $resultSet->first()->subject);
    }

    /**
     * @expectedException \CvoTechnologies\Redmine\Webservice\Exception\UnexpectedStatusCodeException
     */
    public function testReadNotOk()
    {
        $this->webservice->driver()->client($this->_clientMock('get', '/issues.json', [], [
            'issues' => [
                [
                    'subject' => 'Hello'
                ]
            ]
        ], [], 404));
        $query = new Query($this->webservice, new Endpoint([
            'connection' => new Connection([
                'name' => 'redmine',
                'driver' => 'CvoTechnologies\Redmine\Webservice\Driver\Redmine'
            ]),
            'endpoint' => 'issues',
            'schema' => new Schema('issues')
        ]));
        $query->read();

        $this->webservice->execute($query);
    }

    /**
     * @expectedException \CvoTechnologies\Redmine\Webservice\Exception\MissingResultsException
     */
    public function testReadMissingResults()
    {
        $this->webservice->driver()->client($this->_clientMock('get', '/issues.json', [], [
        ], [], 200));
        $query = new Query($this->webservice, new Endpoint([
            'connection' => new Connection([
                'name' => 'redmine',
                'driver' => 'CvoTechnologies\Redmine\Webservice\Driver\Redmine'
            ]),
            'endpoint' => 'issues',
            'schema' => new Schema('issues')
        ]));
        $query->read();

        $this->webservice->execute($query);
    }

    protected function _clientMock($method, $url, $parameters, $response, array $headers = [], $responseCode = 200)
    {
        $responseCodes = [
            200 => 'Ok',
            404 => 'Not Found'
        ];

        $client = $this->getMockBuilder('Cake\Network\Http\Client')
            ->disableOriginalConstructor()
            ->setMethods([$method])
            ->getMock();
        $client
            ->expects($this->once())
            ->method($method)
            ->with($url, $parameters)
            ->willReturn(new Response([
                'HTTP/1.1 ' . $responseCode . ' ' . $responseCodes[$responseCode]
            ] + $headers, json_encode($response)));

        return $client;
    }
}
