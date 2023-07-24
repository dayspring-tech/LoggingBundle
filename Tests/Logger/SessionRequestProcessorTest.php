<?php

namespace Dayspring\LoggingBundle\Tests\Logger;

use Dayspring\LoggingBundle\Logger\SessionRequestProcessor;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionFactory;
use Symfony\Component\Routing\Router;
use function var_dump;

class SessionRequestProcessorTest extends TestCase
{
    public function testProcessor()
    {
        $session = new Session();
        $requestStack = new RequestStack();
        $requestStack->push(Request::create('/', 'GET'));
        $router = $this->createPartialMock(Router::class, ['matchRequest']);
        $router->expects($this->once())
            ->method('matchRequest')
            ->willReturn(['_route' => 'test']);

        $processor = new SessionRequestProcessor($session, $requestStack, $router);

        $handler = new TestHandler();

        $logger = new Logger('test', [$handler], [$processor]);

        $logger->info('test');

        $records = $handler->getRecords();

        $this->assertCount(1, $records);
        $record = $records[0];
        $this->assertEquals('test', $record['context']['route']);
        $this->assertEquals('test', $record['context']['route_parameters']['_route']);
    }
}
