<?php

namespace JMSSerializerModule\config;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\IndexedReader;
use Interop\Container\ContainerInterface;
use JMS\Serializer\Construction\UnserializeObjectConstructor;
use JMS\Serializer\EventDispatcher\Subscriber\DoctrineProxySubscriber;
use JMS\Serializer\Handler\ArrayCollectionHandler;
use JMS\Serializer\Handler\DateHandler;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Metadata\Driver\AnnotationDriver;
use JMS\Serializer\Metadata\Driver\YamlDriver;
use JMS\Serializer\Metadata\Driver\XmlDriver;
use JMS\Serializer\Metadata\Driver\PhpDriver;
use JMS\Serializer\Naming\CamelCaseNamingStrategy;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\XmlDeserializationVisitor;
use JMS\Serializer\XmlSerializationVisitor;
use JMS\Serializer\YamlSerializationVisitor;
use JMSSerializerModule\Metadata\Driver\LazyLoadingDriver;
use JMSSerializerModule\Options\EventDispatcher;
use JMSSerializerModule\Options\Handlers;
use JMSSerializerModule\Options\Metadata;
use JMSSerializerModule\Options\PropertyNaming;
use JMSSerializerModule\Options\Visitors;
use JMSSerializerModule\Service\EventDispatcherFactory;
use JMSSerializerModule\Service\HandlerRegistryFactory;
use JMSSerializerModule\Service\MetadataCacheFactory;
use JMSSerializerModule\Service\MetadataDriverFactory;
use JMSSerializerModule\Service\NamingStrategyFactory;
use JMSSerializerModule\Service\SerializerFactory;
use JMSSerializerModule\View\Serializer;
use Metadata\Cache\CacheInterface;
use Metadata\Driver\DriverChain;
use Metadata\Driver\FileLocator;
use Metadata\MetadataFactory;
use Metadata\ClassHierarchyMetadata;

use RuntimeException;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\ServiceManager\ServiceManager;


return [
    'service_manager' => [
        'aliases' => [
            'jms_serializer.metadata_driver' => DriverChain::class,
            'jms_serializer.object_constructor' => UnserializeObjectConstructor::class,
            'jms_serializer.handler_registry' => HandlerRegistry::class,
            'jms_serializer.datetime_handler' => DateHandler::class,
            'jms_serializer.event_dispatcher' => DateHandler::class,
            'jms_serializer.metadata.cache' => MetadataCacheFactory::class,
            'jms_serializer.metadata.yaml_driver' => YamlDriver::class,
            'jms_serializer.metadata.xml_driver' => XmlDriver::class,
            'jms_serializer.metadata.php_driver' => PhpDriver::class,
            'jms_serializer.metadata.file_locator' => FileLocator::class,
            'jms_serializer.metadata.annotation_driver' => AnnotationDriver::class,
            'jms_serializer.metadata.chain_driver' => DriverChain::class,
            'jms_serializer.metadata.lazy_loading_driver' => LazyLoadingDriver::class,
            'jms_serializer.metadata_factory' => MetadataFactory::class,
            'jms_serializer.camel_case_naming_strategy' => CamelCaseNamingStrategy::class,
            'jms_serializer.identical_naming_strategy' => IdenticalPropertyNamingStrategy::class,
            'jms_serializer.serialized_name_annotation_strategy' => SerializedNameAnnotationStrategy::class,
            'jms_serializer.naming_strategy' => NamingStrategyFactory::class,
            'jms_serializer.serializer' => Serializer::class,
            'jms_serializer.json_serialization_visitor' => JsonSerializationVisitor::class,
            'jms_serializer.json_deserialization_visitor' => JsonDeserializationVisitor::class,
            'jms_serializer.yaml_serialization_visitor' => YamlSerializationVisitor::class,
            'jms_serializer.xml_serialization_visitor' => XmlSerializationVisitor::class,
            'jms_serializer.xml_deserialization_visitor' => XmlDeserializationVisitor::class,
            'jms_serializer.unserialize_object_constructor' => UnserializeObjectConstructor::class,
            'jms_serializer.array_collection_handler' => ArrayCollectionHandler::class,
            'jms_serializer.doctrine_proxy_subscriber' => DoctrineProxySubscriber::class,
        ],
        'factories' => [
            CacheInterface::class => MetadataCacheFactory::class,
            HandlerRegistry::class => HandlerRegistryFactory::class,
            DateHandler::class => function (ContainerInterface $container) {
                $options = $container->get('Configuration');
                $options = new Handlers($options['jms_serializer']['handlers']);
                $dateTimeOptions = $options->getDatetime();

                return new DateHandler($dateTimeOptions['default_format'], $dateTimeOptions['default_timezone']);
            },
            EventDispatcher::class => EventDispatcherFactory::class,
            YamlDriver::class => MetadataDriverFactory::class,
            XmlDriver::class => MetadataDriverFactory::class,
            PhpDriver::class => MetadataDriverFactory::class,
            FileLocator::class => function (ContainerInterface $container, $requestedName) {
                $options = $container->get('Configuration');
                $options = new Metadata($options['jms_serializer']['metadata']);
                $directories = [];

                foreach ($options->getDirectories() as $directory) {
                    if (!isset($directory['path'], $directory['namespace_prefix'])) {
                        throw new RuntimeException(sprintf(
                            'The directory must have the attributes "path" and "namespace_prefix, "%s" given.',
                            implode(', ', array_keys($directory))
                        ));
                    }
                    $directories[rtrim($directory['namespace_prefix'], '\\')] = rtrim($directory['path'], '\\/');
                }

                return new FileLocator($directories);
            },
            AnnotationDriver::class => function (ContainerInterface $container, $requestedName) {
                $options = $container->get('Configuration');
                $options = new Metadata($options['jms_serializer']['metadata']);

                $reader = new AnnotationReader();
                $reader = new CachedReader(
                    new IndexedReader($reader),
                    $container->get($options->getAnnotationCache())
                );

                return new AnnotationDriver($reader);
            },
            DriverChain::class => function (ContainerInterface $container, $requestedName) {
                return new DriverChain([
                    $container->get(YamlDriver::class),
                    $container->get(XmlDriver::class),
                    $container->get(PhpDriver::class),
                    $container->get(AnnotationDriver::class),
                ]);
            },
            LazyLoadingDriver::class => function (ContainerInterface $container, $requestedName) {
                return new LazyLoadingDriver($container, DriverChain::class);
            },
            MetadataFactory::class => function (ContainerInterface $container, $requestedName) {
                $options = $container->get('Configuration');
                $options = new Metadata($options['jms_serializer']['metadata']);
                $lazyLoadingDriver = $container->get(LazyLoadingDriver::class);

                return new MetadataFactory($lazyLoadingDriver, ClassHierarchyMetadata::class, $options->getDebug());
            },
            CamelCaseNamingStrategy::class => function (ContainerInterface $container, $requestedName) {
                $options = $container->get('Configuration');
                $options = new PropertyNaming($options['jms_serializer']['property_naming']);

                return new CamelCaseNamingStrategy($options->getSeparator(), $options->getLowercase());
            },
            IdenticalPropertyNamingStrategy::class => InvokableFactory::class,
            SerializedNameAnnotationStrategy::class => function (ContainerInterface $container, $requestedName) {
                $options = $container->get('Configuration');
                $namingStrategy = isset($options['jms_serializer']['naming_strategy'])
                    ? $options['jms_serializer']['naming_strategy']
                    : CamelCaseNamingStrategy::class;

                // todo PropertyNamingStrategyInterface

                return new SerializedNameAnnotationStrategy($container->get($namingStrategy));
            },
            NamingStrategyFactory::class => NamingStrategyFactory::class,
            JsonSerializationVisitor::class => function (ContainerInterface $container, $requestedName) {
                $options = $container->get('Configuration');
                $options = new Visitors($options['jms_serializer']['visitors']);

                $jsonOptions = $options->getJson();
                $visitor = new JsonSerializationVisitor($container->get(NamingStrategyFactory::class));
                $visitor->setOptions($jsonOptions['options']);

                return $visitor;
            },
            JsonDeserializationVisitor::class => function (ContainerInterface $container, $requestedName) {
                return new JsonDeserializationVisitor(
                    $container->get(NamingStrategyFactory::class),
                    $container->get(UnserializeObjectConstructor::class)
                );
            },
            XmlSerializationVisitor::class => function (ContainerInterface $container, $requestedName) {
                return new XmlSerializationVisitor(
                    $container->get(NamingStrategyFactory::class)
                );
            },
            XmlDeserializationVisitor::class => function (ContainerInterface $container, $requestedName) {
                $options = $container->get('Configuration');
                $options = new Visitors($options['jms_serializer']['visitors']);

                $xmlOptions = $options->getXml();
                $visitor = new XmlDeserializationVisitor(
                    $container->get(NamingStrategyFactory::class),
                    $container->get(UnserializeObjectConstructor::class)
                );
                $visitor->setDoctypeWhitelist($xmlOptions['doctype_whitelist']);

                return $visitor;
            },
            YamlSerializationVisitor::class => function (ContainerInterface $container, $requestedName) {
                return new YamlSerializationVisitor($container->get(NamingStrategyFactory::class));
            },
            Serializer::class => SerializerFactory::class,
            UnserializeObjectConstructor::class => InvokableFactory::class,
            ArrayCollectionHandler::class => InvokableFactory::class,
            DoctrineProxySubscriber::class => InvokableFactory::class,
        ],
    ],
];