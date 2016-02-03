<?php

namespace CvoTechnologies\Redmine\Model;

use Cake\Event\Event;
use CvoTechnologies\Redmine\Model\Endpoint\CustomFieldsEndpoint;
use CvoTechnologies\Redmine\Schema;

class Endpoint extends \Muffin\Webservice\Model\Endpoint
{

    /**
     * @var \CvoTechnologies\Redmine\Schema
     */
    protected $_baseSchema;

    /**
     * @param \CvoTechnologies\Redmine\Schema|null $schema
     *
     * @return \CvoTechnologies\Redmine\Schema
     */
    public function baseSchema($schema = null)
    {
        if (($schema === null) && ($this->_baseSchema === null)) {
            $this->_baseSchema = new Schema($this->endpoint());
        }

        if ($schema) {
            $this->_baseSchema = $schema;

            return $this;
        }

        return $this->_baseSchema;
    }

    public function schema($schema = null)
    {
        if ($schema === null) {
            if ($this->_schema === null) {
                $customFieldsEndpoint = new CustomFieldsEndpoint([
                    'connection' => $this->connection()
                ]);

                $this->_schema = $customFieldsEndpoint->alterSchema($this->baseSchema());
            }

            return $this->_schema;
        }

        return parent::schema($schema);
    }
}
