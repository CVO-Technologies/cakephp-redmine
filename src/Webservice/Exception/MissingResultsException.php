<?php

namespace CvoTechnologies\Redmine\Webservice\Exception;

use CvoTechnologies\Redmine\Webservice\Exception;

class MissingResultsException extends Exception
{

    protected $_messageTemplate = 'Missing results From Redmine API (%s)';
}
