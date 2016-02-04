<?php

namespace CvoTechnologies\Redmine\Webservice\Exception;

use CvoTechnologies\Redmine\Webservice\Exception;

class UnexpectedStatusCodeException extends Exception
{

    protected $_messageTemplate = 'Unexpected status code %d from Redmine API';
}
