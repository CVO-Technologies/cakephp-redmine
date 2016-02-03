<?php

namespace CvoTechnologies\Redmine\Webservice\Driver;

use Cake\Network\Http\Client;
use Muffin\Webservice\AbstractDriver;

class Redmine extends AbstractDriver
{

    /**
     * Initialize is used to easily extend the constructor.
     *
     * @return void
     */
    public function initialize()
    {
        $clientOptions = is_array($this->config('client')) ? $this->config('client') : [];
        $clientOptions['host'] = $this->config('host');
        $clientOptions['scheme'] = $this->config('scheme');

        $this->client(new Client($clientOptions));
    }
}
