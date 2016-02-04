<?php

namespace CvoTechnologies\Redmine\Webservice;

use Cake\Utility\Inflector;
use CvoTechnologies\Redmine\Schema;
use CvoTechnologies\Redmine\Webservice\Exception\MissingResultsException;
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

    protected function _executeReadQuery(Query $query, array $options = [])
    {
        $url = $this->getBaseUrl();

        $requestParameters = [];
        $requestOptions = [];
        if ($this->driver()->config('api_key')) {
            $requestParameters['key'] = $this->driver()->config('api_key');
        }

        // Single resource
        if ((isset($query->clause('where')['id'])) && (!is_array($query->clause('where')['id']))) {
            $url .= '/' . $query->clause('where')['id'];
        } else {
            foreach ($query->clause('where') as $field => $value) {
                $column = $query->endpoint()->schema()->column($field);
                if ($column['custom_field_id']) {
                    $field = 'cf_' . $column['custom_field_id'];
                }

                $requestParameters[$field] = $value;
            }
        }

        $url .= '.json';

        $orderClauses = [];
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

        if (isset($query->getOptions()['include'])) {
            $include = $query->getOptions()['include'];
            if (!is_array($include)) {
                $include = [$include];
            }

            $requestParameters['include'] = implode(',', $include);
        }
        if (isset($query->getOptions()['user'])) {
            $requestOptions['headers']['X-Redmine-Switch-User'] = $query->getOptions()['user'];
        }

        /* @var \Cake\Network\Http\Response $response */
        $response = $this->driver()->client()->get($url, $requestParameters, $requestOptions);
        if (!$response->isOk()) {
            return false;
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
                foreach ($value as $customField) {
                    $customFieldField = Schema::nameToField($customField['name']);
                    $column = $endpoint->schema()->column($customFieldField);

                    if (!isset($customField['value'])) {
                        $properties[$customFieldField] = null;

                        continue;
                    }
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
