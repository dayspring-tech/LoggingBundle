<?php

namespace Dayspring\LoggingBundle\Tests;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel
{
    public function getProjectDir(): string
    {
        return __DIR__;
    }

    public function getCacheDir(): string
    {
        return __DIR__.'/../var/cache';
    }

    public function getLogDir(): string
    {
        return __DIR__.'/../var/log';
    }

    public function registerBundles(): iterable
    {
        $bundles = [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Dayspring\LoggingBundle\DayspringLoggingBundle()
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
        }

        return $bundles;
    }
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/Resources/config/config_test.yml');
        if (class_exists(\Symfony\Component\Asset\Package::class)) {
            $loader->load(function (ContainerBuilder $container) {
                $container->loadFromExtension('framework', ['assets' => []]);
            });
        }
    }
}
