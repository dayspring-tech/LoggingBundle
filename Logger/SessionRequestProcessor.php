<?php

namespace Dayspring\LoggingBundle\Logger;

use Exception;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

class SessionRequestProcessor
{

    /** @var SessionInterface $session */
    private $session;
    /** @var RequestStack $requestStack */
    private $requestStack;
    /** @var UrlMatcherInterface|RequestMatcherInterface $matcher */
    private $matcher;

    private $sessionId;
    private $requestId;
    private $_server;
    private $_get;
    private $_post;

    public function __construct(SessionInterface $session, RequestStack $requestStack, UrlMatcherInterface $matcher)
    {
        $this->session = $session;
        $this->requestStack = $requestStack;
        $this->matcher = $matcher;
    }

    protected function getServerVar($var)
    {
        return isset($_SERVER[$var]) ? $_SERVER[$var] : null;
    }

    public function __invoke(array $record)
    {
        if (null === $this->requestId) {
            if ('cli' === php_sapi_name()) {
                $this->sessionId = getmypid();
            } else {
                try {
                    $this->session->start();
                    $this->sessionId = $this->session->getId();
                } catch (\RuntimeException $e) {
                    $this->sessionId = '????????';
                }
            }
            $this->requestId = substr(uniqid(), -8);
            $this->_server = array(
                'http.url' => ($this->getServerVar('HTTP_HOST')).'/'.($this->getServerVar('REQUEST_URI')),
                'http.method' => $this->getServerVar('REQUEST_METHOD'),
                'http.useragent' => $this->getServerVar('HTTP_USER_AGENT'),
                'http.referer' => $this->getServerVar('HTTP_REFERER'),
                'http.x_forwarded_for' => $this->getServerVar('HTTP_X_FORWARDED_FOR')
            );
            $this->_post = $this->clean($_POST);
            $this->_get = $this->clean($_GET);
        }
        $record['http.request_id'] = $this->requestId;
        $record['http.session_id'] = $this->sessionId;
        $record['http.url'] = $this->_server['http.url'];
        $record['http.method'] = $this->_server['http.method'];
        $record['http.useragent'] = $this->_server['http.useragent'];
        $record['http.referer'] = $this->_server['http.referer'];
        $record['http.x_forwarded_for'] = $this->_server['http.x_forwarded_for'];

        if ($this->requestStack->getMasterRequest()) {
            $request = $this->requestStack->getMasterRequest();
            $context = [
                'request_uri'      => $request->getUri(),
                'method'           => $request->getMethod(),
            ];
            try {
                if ($this->matcher instanceof RequestMatcherInterface) {
                    $parameters = $this->matcher->matchRequest($request);
                } else {
                    $parameters = $this->matcher->match($request->getPathInfo());
                }
                $context['route'] = isset($parameters['_route']) ? $parameters['_route'] : 'n/a';
                $context['route_parameters'] = $parameters;
            } catch (Exception $e) {
            }
            if (array_key_exists('context', $record)) {
                $record['context'] = array_merge($record['context'], $context);
            } else {
                $record['context'] = $context;
            }
        }

        return $record;
    }

    protected function clean($array)
    {
        $toReturn = array();
        foreach (array_keys($array) as $key) {
            if (false !== strpos($key, 'password')) {
                // Do not add
            } elseif (false !== strpos($key, 'csrf_token')) {
                // Do not add
            } else {
                $toReturn[$key] = $array[$key];
            }
        }

        return $toReturn;
    }
}
