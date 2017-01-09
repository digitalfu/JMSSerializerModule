<?php

namespace JMSSerializerModule\Service;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use JMS\Serializer\Naming\CacheNamingStrategy;
use JMSSerializerModule\Options\PropertyNaming;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @author Martin Parsiegla <martin.parsiegla@gmail.com>
 */
class NamingStrategyFactory extends AbstractFactory
{
    /**
     * {@inheritdoc}
     */
    public function getOptionsClass()
    {
        return PropertyNaming::class;
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
        /** @var $options \JMSSerializerModule\Options\PropertyNaming */
        $options = $this->getOptions($container, 'property_naming');
        /** @var $namingStrategy \JMS\Serializer\Naming\PropertyNamingStrategyInterface */
        $namingStrategy = $container->get('jms_serializer.serialized_name_annotation_strategy');
        if ($options->getEnableCache()) {
            $namingStrategy = new CacheNamingStrategy($namingStrategy);
        }

        return $namingStrategy;
    }
}
