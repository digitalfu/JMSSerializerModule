<?php

namespace JMSSerializerModule\Service;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use JMS\Serializer\Metadata\Driver\PhpDriver;
use JMS\Serializer\Metadata\Driver\XmlDriver;
use JMS\Serializer\Metadata\Driver\YamlDriver;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;


/**
 * @author Martin Parsiegla <martin.parsiegla@gmail.com>
 */
class MetadataDriverFactory implements FactoryInterface
{
    /**
     * Get available drivers
     *
     * @return array
     */
    protected function getAvailableDrivers() {
        return [
            YamlDriver::class,
            XmlDriver::class,
            PhpDriver::class
        ];
    }

    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string             $requestedName
     * @param  null|array         $options
     * @return object
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     *     creating a service.
     * @throws ContainerException if any other error occurs
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if(!in_array($requestedName, $this->getAvailableDrivers())) {
            throw new ServiceNotCreatedException(sprintf(
                "Requested class is not in getAvailableDrivers()"
            ));
        }

        $fileLocator = $container->get('jms_serializer.metadata.file_locator');
        $driver = new $requestedName($fileLocator);

        return $driver;
    }
}
