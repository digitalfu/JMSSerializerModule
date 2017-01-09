<?php

namespace JMSSerializerModule\Service;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use JMSSerializerModule\Options\Metadata;
use Metadata\Cache\FileCache;
use RuntimeException;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @author Martin Parsiegla <martin.parsiegla@gmail.com>
 */
class MetadataCacheFactory extends AbstractFactory
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
        /** @var $options Metadata */
        $options = $this->getOptions($container, 'metadata');
        if ($options->getCache() == 'none') {
            return null;
        } elseif ($options->getCache() == 'file') {
            $fileCache = $options->getFileCache();
            $dir = $fileCache['dir'];
            if (!file_exists($dir)) {
                if (!$rs = @mkdir($dir, 0777, true)) {
                    throw new RuntimeException(sprintf('Could not create cache directory "%s".', $dir));
                }
            }
            return new FileCache($dir);
        }

        return $container->get($options->getCache());
    }

    /**
     * {@inheritDoc}
     */
    public function getOptionsClass()
    {
        return Metadata::class;
    }
}
