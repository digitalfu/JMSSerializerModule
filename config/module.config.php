<?php
namespace JMSSerializerModule\config;

use DateTime;
use JMS\Serializer\EventDispatcher\Subscriber\DoctrineProxySubscriber;
use JMS\Serializer\Handler\ArrayCollectionHandler;
use JMS\Serializer\Handler\DateHandler;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\XmlDeserializationVisitor;
use JMS\Serializer\XmlSerializationVisitor;
use JMS\Serializer\YamlSerializationVisitor;

return [
    'jms_serializer' => [
        'handlers' => [
            'datetime' => [
                'default_format' => DateTime::ISO8601,
                'default_timezone' => date_default_timezone_get(),
            ],
            'subscribers' => [
                DateHandler::class,
                ArrayCollectionHandler::class,
            ],
        ],
        'event_dispatcher' => [
            'subscribers' => [
                DoctrineProxySubscriber::class,
            ],
        ],
        'property_naming' => [
            'separator' => '_',
            'lower_case' => true,
            'enable_cache' => true,
        ],
        'metadata' => [
            'cache' => 'file',
            'annotation_cache' => 'array',
            'debug' => false,
            'file_cache' => [
                'dir' => 'data/JMSSerializerModule',
            ],
            'infer_types_from_doctrine_metadata' => true,
            'directories' => [],
        ],
        'visitors' => [
            'json' => [
                'options' => 0,
            ],
            'xml' => [
                'doctype_whitelist' => [],
            ],
            'serialization' => [
                'json' => JsonSerializationVisitor::class,
                'xml'  => XmlSerializationVisitor::class,
                'yml'  => YamlSerializationVisitor::class,
            ],
            'deserialization' => [
                'json' => JsonDeserializationVisitor::class,
                'xml'  => XmlDeserializationVisitor::class,
            ],
        ],
    ],
];
