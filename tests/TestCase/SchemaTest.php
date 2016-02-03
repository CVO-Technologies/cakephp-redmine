<?php

namespace CvoTechnologies\Redmine\Test\TestCase;

use Cake\TestSuite\TestCase;
use CvoTechnologies\Redmine\Schema;

class SchemaTest extends TestCase
{

    /**
     * @var \CvoTechnologies\Redmine\Schema
     */
    public $schema;

    public function setUp()
    {
        parent::setUp();

        $this->schema = new Schema('issues');
    }

    public function testNameToField()
    {
        $this->assertEquals('quote_send', $this->schema->nameToField('Quote send'));
        $this->assertEquals('invoice_send', $this->schema->nameToField('Invoice send'));
        $this->assertEquals('invoice', $this->schema->nameToField('Invoice'));
    }
}
