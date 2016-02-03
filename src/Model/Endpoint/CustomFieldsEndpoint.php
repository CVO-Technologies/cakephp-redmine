<?php

namespace CvoTechnologies\Redmine\Model\Endpoint;

use Cake\Utility\Inflector;
use CvoTechnologies\Redmine\Model\Endpoint;
use CvoTechnologies\Redmine\Schema;

class CustomFieldsEndpoint extends Endpoint
{

    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->schema(new Schema($this->endpoint()));
    }

    public function alterSchema(Schema $schema)
    {
        $customizedType = Inflector::singularize($schema->name());

        $customFields = $this->find()->cache('custom_fields_' . $this->connection()->configName());

        /** @var \Muffin\Webservice\Model\Resource[] $customFields */
        foreach ($customFields as $customField) {
            if ($customField->customized_type !== $customizedType) {
                continue;
            }

            $columnKeys = [
                'type' => $customField->field_format,
                'default' => $customField->default_value,
                'custom_field_id' => $customField->id,
                'custom_field_filterable' => $customField->is_filter
            ];
            $schema->addColumn(Schema::nameToField($customField->name), $columnKeys);
        }

        return $schema;
    }
}
