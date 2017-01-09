<?php

namespace JMSSerializerModule\Service;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use InvalidArgumentException;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\ServiceLocatorInterface;
use JMSSerializerModule\Options\Handlers;

/**
 * @author Martin Parsiegla <martin.parsiegla@gmail.com>
 */
class HandlerRegistryFactory extends AbstractFactory
{
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
        /** @var $options Handlers */
        $options      = $this->getOptions($container, 'handlers');
        $handlerRegistry = new HandlerRegistry();

        foreach ($options->getSubscribers() as $subscriberName) {
            $subscriber = $subscriberName;
            if (is_string($subscriber)) {
                if ($container->has($subscriber)) {
                    $subscriber = $container->get($subscriber);
                } elseif (class_exists($subscriber)) {
                    $subscriber = new $subscriber();
                }
            }

            if ($subscriber instanceof SubscribingHandlerInterface) {
                $handlerRegistry->registerSubscribingHandler($subscriber);
                continue;
            }
            throw new InvalidArgumentException(sprintf(
                'Invalid subscriber "%s" given, must be a service name, '
                . 'class name or an instance implementing JMS\Serializer\Handler\SubscribingHandlerInterface;
',
                is_object($subscriberName)
                    ? get_class($subscriberName)
                    : (is_string($subscriberName) ? $subscriberName : gettype($subscriber))
            ));
        }

        return $handlerRegistry;
    }

    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $container)
    {

    }

    /**
     * {@inheritDoc}
     */
    public function getOptionsClass()
    {
        return Handlers::class;
    }


}
