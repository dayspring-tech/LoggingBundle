<?php

namespace Service;

use Dayspring\LoggingBundle\Logger\SessionRequestProcessor;
use Dayspring\LoggingBundle\Tests\TestKernel;
use Monolog\Handler\TestHandler;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoggerTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
    }

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    public function testLogger()
    {
        $logger = static::$kernel->getContainer()->get('test_logger');

        $logger->info('This is a test log message');

        $testHandler = static::$kernel->getContainer()->get(TestHandler::class);
        $records = $testHandler->getRecords();

        $this->assertGreaterThan(0, count($records));

        $record = array_pop($records);

        $this->assertArrayHasKey('message', $record);
        $this->assertArrayHasKey('http.session_id', $record);
        $this->assertArrayHasKey('http.request_id', $record);
    }

    public function testExtraFields()
    {
        $logger = static::$kernel->getContainer()->get('test_logger');

        /** @var SessionRequestProcessor $sessionRequestProcessor */
        $sessionRequestProcessor = static::$kernel->getContainer()->get('test_session_request_processor');
        $sessionRequestProcessor->setExtraField('test',  'value');

        $logger->info('This is a test log message');

        $testHandler = static::$kernel->getContainer()->get(TestHandler::class);
        $records = $testHandler->getRecords();

        $this->assertGreaterThan(0, count($records));

        $record = array_pop($records);
        $this->assertArrayHasKey('test', $record);
        $this->assertEquals('value', $record['test']);
    }

    public function testUnsetExtraFields()
    {
        $logger = static::$kernel->getContainer()->get('test_logger');

        /** @var SessionRequestProcessor $sessionRequestProcessor */
        $sessionRequestProcessor = static::$kernel->getContainer()->get('test_session_request_processor');
        $sessionRequestProcessor->setExtraField('test',  'value');

        $logger->info('This is a test log message');

        $sessionRequestProcessor->unsetExtraField('test');

        $logger->info('This is a test log message without extra field');

        $testHandler = static::$kernel->getContainer()->get(TestHandler::class);
        $records = $testHandler->getRecords();

        $this->assertGreaterThan(0, count($records));

        $record = array_pop($records);
        $this->assertEquals('This is a test log message without extra field', $record['message']);
        $this->assertArrayNotHasKey('test', $record);
    }

    public function testClearExtraFields()
    {
        $logger = static::$kernel->getContainer()->get('test_logger');

        /** @var SessionRequestProcessor $sessionRequestProcessor */
        $sessionRequestProcessor = static::$kernel->getContainer()->get('test_session_request_processor');
        $sessionRequestProcessor->setExtraField('test',  'value');

        $logger->info('This is a test log message');

        $sessionRequestProcessor->clearExtraFields();

        $logger->info('This is a test log message without extra field');

        $testHandler = static::$kernel->getContainer()->get(TestHandler::class);
        $records = $testHandler->getRecords();

        $this->assertGreaterThan(0, count($records));

        $record = array_pop($records);
        $this->assertEquals('This is a test log message without extra field', $record['message']);
        $this->assertArrayNotHasKey('test', $record);
    }

}
