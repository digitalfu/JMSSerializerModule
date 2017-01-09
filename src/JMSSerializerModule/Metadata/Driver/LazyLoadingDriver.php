<?php

namespace JMSSerializerModule\Metadata\Driver;

use Interop\Container\ContainerInterface;
use Metadata\Driver\DriverInterface;
use ReflectionClass;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @author Martin Parsiegla <martin.parsiegla@gmail.com>
 */
class LazyLoadingDriver implements DriverInterface
{
    private $container;
    private $realDriverId;

    public function __construct(ContainerInterface $container, $realDriverId)
    {
        $this->container = $container;
        $this->realDriverId = $realDriverId;
    }


    /**
     * {@ineheritdoc}
     */
    public function loadMetadataForClass(ReflectionClass $class)
    {
        return $this->container->get($this->realDriverId)->loadMetadataForClass($class);
    }
}
