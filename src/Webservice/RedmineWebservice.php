<?php

namespace CvoTechnologies\Redmine\Webservice;

use Cake\Utility\Inflector;
use CvoTechnologies\Redmine\Schema;
use CvoTechnologies\Redmine\Webservice\Exception\MissingResultsException;
use CvoTechnologies\Redmine\Webservice\Exception\UnexpectedStatusCodeException;
use Muffin\Webservice\Model\Endpoint;
use Muffin\Webservice\Query;
use Muffin\Webservice\ResultSet;
use Muffin\Webservice\Webservice\Webservice;

class RedmineWebservice extends Webservice
{

    /**
     * Returns the base URL for this endpoint
     *
     * @return string Base URL
     */
    public function getBaseUrl()
    {
        return '/' . $this->endpoint();
    }

    /**
     * {@inheritDoc}
     */
    protected function _executeReadQuery(Query $query, array $options = [])
    {
        $url = $this->getBaseUrl();

        $requestParameters = [];
        $requestOptions = [];

        // Set API key if necessary
        if ($this->driver()->config('api_key')) {
            $requestParameters['key'] = $this->driver()->config('api_key');
        }

        // Single resource
        if ((isset($query->clause('where')['id'])) && (!is_array($query->clause('where')['id']))) {
            $url .= '/' . $query->clause('where')['id'];
        } else {
            // Loop over conditions and apply them to the query
            foreach ($query->clause('where') as $field => $value) {
                $column = $query->endpoint()->schema()->column($field);

                // Custom fields should use cf_x as parameter
                if ($column['custom_field_id']) {
                    $field = 'cf_' . $column['custom_field_id'];
                }

                $requestParameters[$field] = $value;
            }
        }

        $url .= '.json';

        $orderClauses = [];
        // Turn ORM order clauses in a query parameter
        foreach ($query->clause('order') as $field => $value) {
            if (is_int($field)) {
                $field = $value;
            }
            if (!in_array($value, ['ASC', 'DESC'])) {
                $value = null;
            }

            if (!$value) {
                $orderClauses[] = $field;

                continue;
            }

            // Replace ASC with asc and DESC with desc
            $orderClauses[] = $field . ':' . str_replace(['ASC', 'DESC'], ['asc', 'desc'], $value);
        }

        if (!empty($orderClauses)) {
            $requestParameters['sort'] = implode($orderClauses, ',');
        }

        if ($query->clause('limit')) {
            $requestParameters['limit'] = $query->clause('limit');
        }
        if ($query->clause('offset')) {
            $requestParameters['offset'] = $query->clause('offset');
        }

        // Include details using the API
        if (isset($query->getOptions()['include'])) {
            $include = $query->getOptions()['include'];
            // If the value isn't a array, for example a string put it in a string
            if (!is_array($include)) {
                $include = [$include];
            }

            $requestParameters['include'] = implode(',', $include);
        }

        // Switch user if the 'user' options has been given
        if (isset($query->getOptions()['user'])) {
            $requestOptions['headers']['X-Redmine-Switch-User'] = $query->getOptions()['user'];
        }

        /* @var \Cake\Network\Http\Response $response */
        $response = $this->driver()->client()->get($url, $requestParameters, $requestOptions);
        if (!$response->isOk()) {
            throw new UnexpectedStatusCodeException([$response->statusCode()]);
        }

        // Single resource
        if (isset($query->clause('where')['id'])) {
            // Turn result into resources
            $resource = $this->_transformResource($response->json[Inflector::singularize($this->endpoint())], $query->endpoint());

            return new ResultSet([$resource], 1);
        }

        if (!isset($response->json[$this->endpoint()])) {
            throw new MissingResultsException([$url]);
        }

        $results = $response->json[$this->endpoint()];

        $total = count($results);
        // Set the amount if results to total_count if it has been provided by the API
        if (isset($response->json['total_count'])) {
            $total = $response->json['total_count'];
        }

        // Turn results into resources
        $resources = $this->_transformResults($results, $query->endpoint());
        return new ResultSet($resources, $total);
    }

    protected function _transformResource(array $result, Endpoint $endpoint)
    {
        $properties = [];

        foreach ($result as $field => $value) {
            if ($field === 'custom_fields') {
                // Loop over custom fields
                foreach ($value as $customField) {
                    // Get the alias for the custom field
                    $customFieldField = Schema::nameToField($customField['name']);

                    // Lookup the field in the schema
                    $column = $endpoint->schema()->column($customFieldField);

                    // If no value has been given set it to null
                    if (!isset($customField['value'])) {
                        $properties[$customFieldField] = null;

                        continue;
                    }

                    // Cast value to correct type and set it as property
                    $properties[$customFieldField] = $this->castValue($customField['value'], $column['type']);
                }

                continue;
            }

            $column = $endpoint->schema()->column($field);
            if (!$column) {
                $properties[$field] = $value;

                continue;
            }

            $properties[$field] = $this->castValue($value, $column['type']);
        }

        return $this->_createResource($endpoint->resourceClass(), $properties);
    }

    /**
     * Cast value from API to PHP types
     *
     * @param mixed $value Vaue to cast
     * @param string $type Type to convert the value to
     *
     * @return string|bool|int Casted value
     */
    public function castValue($value, $type)
    {
        switch ($type) {
            case 'link':
                return $value;
            case 'bool':
                return (bool)$value;
            case 'int':
                return (int)$value;
        }

        return $value;
    }
}
