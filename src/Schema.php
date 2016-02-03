<?php

namespace CvoTechnologies\Redmine;

use Cake\Utility\Inflector;

class Schema extends \Muffin\Webservice\Schema
{

    /**
     * The valid keys that can be used in a column
     * definition.
     *
     * @var array
     */
    protected static $_columnKeys = [
        'type' => null,
        'baseType' => null,
        'length' => null,
        'precision' => null,
        'null' => null,
        'default' => null,
        'comment' => null,
        'custom_field_id' => null,
        'custom_field_filterable' => null
    ];

    public static function nameToField($name)
    {
        return Inflector::underscore(Inflector::classify($name));
    }
}
