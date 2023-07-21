<?php
/**
 * Created by PhpStorm.
 * User: jwong
 * Date: 3/17/16
 * Time: 2:18 PM
 */

namespace Dayspring\LoggingBundle\Tests\DependencyInjection;

use Dayspring\LoggingBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{

    public function testConfiguration()
    {
        $config = array();

        $processor = new Processor();
        $configuration = new Configuration(array());
        $config = $processor->processConfiguration($configuration, array($config));

        $this->assertEquals([
            'session_request_processor_handlers' => []
        ], $config, 'Config should be empty');
    }
}
