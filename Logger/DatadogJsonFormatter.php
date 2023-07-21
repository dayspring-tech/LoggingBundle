<?php

namespace Dayspring\LoggingBundle\Logger;

use Monolog\Formatter\JsonFormatter;

class DatadogJsonFormatter extends JsonFormatter
{
    public function format(array $record)
    {
        // translate a DateTime object into a string so Datadog can parse it
        // 2020-10-14T17:57:05.971-0700
        $record['timestamp'] = $record['datetime']->format('Y-m-d\TH:i:s.vO');

        return parent::format($record);
    }
}
