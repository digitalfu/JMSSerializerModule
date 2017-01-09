<?php

/*
 * This file is part of the JMSSerializerModule package.
 *
 * (c) Martin Parsiegla <martin.parsiegla@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JMSSerializerModule;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\IndexedReader;
use JMS\Serializer\Handler\DateHandler;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Metadata\Driver\AnnotationDriver;
use JMS\Serializer\Naming\CamelCaseNamingStrategy;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\XmlDeserializationVisitor;
use JMS\Serializer\XmlSerializationVisitor;
use JMS\Serializer\YamlSerializationVisitor;
use JMSSerializerModule\Metadata\Driver\LazyLoadingDriver;
use JMSSerializerModule\Options\Handlers;
use JMSSerializerModule\Options\Metadata;
use JMSSerializerModule\Options\PropertyNaming;
use JMSSerializerModule\Options\Visitors;
use JMSSerializerModule\Service\EventDispatcherFactory;
use JMSSerializerModule\Service\HandlerRegistryFactory;
use JMSSerializerModule\Service\MetadataCacheFactory;
use JMSSerializerModule\Service\MetadataDriverFactory;
use JMSSerializerModule\View\Serializer;
use Metadata\Driver\DriverChain;
use Metadata\Driver\FileLocator;
use Metadata\MetadataFactory;
use Zend\Loader\AutoloaderFactory;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Loader\StandardAutoloader;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Base module for JMS Serializer
 *
 * @author Martin Parsiegla <martin.parsiegla@gmail.com>
 */
class Module implements
    AutoloaderProviderInterface,
    ConfigProviderInterface,
    ServiceProviderInterface,
    ViewHelperProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function getAutoloaderConfig()
    {
        return array(
            AutoloaderFactory::STANDARD_AUTOLOADER => array(
                StandardAutoloader::LOAD_NS => array(
                    __NAMESPACE__ => __DIR__,
                ),
            ),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    /**
     * {@inheritDoc}
     */
    public function getServiceConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    /**
     * {@inheritdoc}
     */
    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                'jmsSerializer' => function ($helpers) {
                    $sm = $helpers->getServiceLocator();
                    $viewHelper = new Serializer($sm->get('jms_serializer.serializer'));

                    return $viewHelper;
                },
            ),
        );
    }
}
