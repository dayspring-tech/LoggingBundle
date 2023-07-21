<?php

namespace Dayspring\LoggingBundle\DependencyInjection;

use Dayspring\LoggingBundle\Logger\SessionRequestProcessor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class DayspringLoggingExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        foreach ($config['session_request_processor_handlers'] as $handler) {
            $definition = new Definition(SessionRequestProcessor::class);
            $definition->addTag('monolog.processor', array('handler' => $handler));
            $definition->setAutowired(true);

            $container->addDefinitions(array(
                'dayspring_logging.session_request_processor.'.$handler => $definition
            ));
        }
    }
}
