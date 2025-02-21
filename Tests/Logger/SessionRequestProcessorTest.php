<?php

namespace Dayspring\LoggingBundle\Tests\Logger;

use Dayspring\LoggingBundle\Logger\SessionRequestProcessor;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;
use Symfony\Component\Routing\Router;
use function var_dump;

class SessionRequestProcessorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_SERVER['SERVER_NAME'] = 'test';
    }

    public function testProcessor()
    {
        $request = Request::create('/', 'GET');
        $request->setSession(new Session(new MockFileSessionStorage()));

        $requestStack = new RequestStack();
        $requestStack->push($request);
        $router = $this->createPartialMock(Router::class, ['matchRequest']);
        $router->expects($this->once())
            ->method('matchRequest')
            ->willReturn(['_route' => 'test']);

        $processor = new SessionRequestProcessor($requestStack, $router);

        $handler = new TestHandler();

        $logger = new Logger('test', [$handler], [$processor]);

        $logger->info('test');

        $records = $handler->getRecords();

        $this->assertCount(1, $records);
        $record = $records[0];
        $this->assertEquals('test', $record['context']['route']);
        $this->assertEquals('test', $record['context']['route_parameters']['_route']);
        $this->assertArrayHasKey('http.session_id', $record);
    }

    public function testProcessorCli()
    {
        unset($_SERVER['SERVER_NAME']);

        $requestStack = new RequestStack();
        $router = $this->createPartialMock(Router::class, ['matchRequest']);

        $processor = new SessionRequestProcessor($requestStack, $router);

        $handler = new TestHandler();

        $logger = new Logger('test', [$handler], [$processor]);

        $logger->info('test');

        $records = $handler->getRecords();

        $this->assertCount(1, $records);
        $record = $records[0];
        $this->assertArrayHasKey('http.session_id', $record);
        $this->assertEquals($record['http.session_id'], getmypid());

    }

    public function testProcessorNoRequest()
    {
        $requestStack = new RequestStack();
        $router = $this->createPartialMock(Router::class, ['matchRequest']);

        $processor = new SessionRequestProcessor($requestStack, $router);

        $handler = new TestHandler();

        $logger = new Logger('test', [$handler], [$processor]);

        $logger->info('test');

        $records = $handler->getRecords();

        $this->assertCount(1, $records);
        $record = $records[0];
        $this->assertArrayNotHasKey('route', $record['context']);
        $this->assertNull($record['http.session_id']);
    }


    public function testProcessorRequestAddedLater()
    {
        $requestStack = new RequestStack();
        $router = $this->createPartialMock(Router::class, ['matchRequest']);

        $processor = new SessionRequestProcessor($requestStack, $router);

        $handler = new TestHandler();

        $logger = new Logger('test', [$handler], [$processor]);

        $logger->info('test');

        $request = Request::create('/', 'GET');
        $request->setSession(new Session(new MockFileSessionStorage()));
        $requestStack->push($request);

        $logger->info('test with session');

        $records = $handler->getRecords();

        $this->assertCount(2, $records);
        $record = $records[0];
        $this->assertArrayNotHasKey('route', $record['context']);
        $this->assertNull($record['http.session_id']);

        $record2 = $records[1];
        $this->assertArrayHasKey('route', $record2['context']);
        $this->assertNotNull($record2['http.session_id']);
        $this->assertEquals($record['http.request_id'], $record2['http.request_id']);
    }
}
