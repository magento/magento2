<?php
/**
 * @link      http://github.com/zendframework/zend-mvc for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-mvc/blob/master/LICENSE.md New BSD License
 * @SuppressWarnings(PHPMD)
 */

declare(strict_types=1);

namespace Zend\Mvc\Controller;

use Interop\Container\ContainerInterface;
use ReflectionClass;
use ReflectionParameter;
use Zend\Console\Adapter\AdapterInterface as ConsoleAdapterInterface;
use Zend\Filter\FilterPluginManager;
use Zend\Hydrator\HydratorPluginManager;
use Zend\InputFilter\InputFilterPluginManager;
use Zend\Log\FilterPluginManager as LogFilterManager;
use Zend\Log\FormatterPluginManager as LogFormatterManager;
use Zend\Log\ProcessorPluginManager as LogProcessorManager;
use Zend\Log\WriterPluginManager as LogWriterManager;
use Zend\Serializer\AdapterPluginManager as SerializerAdapterManager;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\DispatchableInterface;
use Zend\Validator\ValidatorPluginManager;

/**
 * Reflection-based factory for controllers.
 *
 * To ease development, this factory may be used for controllers with
 * type-hinted arguments that resolve to services in the application
 * container; this allows omitting the step of writing a factory for
 * each controller.
 *
 * You may use it as either an abstract factory:
 *
 * <code>
 * 'controllers' => [
 *     'abstract_factories' => [
 *         LazyControllerAbstractFactory::class,
 *     ],
 * ],
 * </code>
 *
 * Or as a factory, mapping a controller class name to it:
 *
 * <code>
 * 'controllers' => [
 *     'factories' => [
 *         MyControllerWithDependencies::class => LazyControllerAbstractFactory::class,
 *     ],
 * ],
 * </code>
 *
 * The latter approach is more explicit, and also more performant.
 *
 * The factory has the following constraints/features:
 *
 * - A parameter named `$config` typehinted as an array will receive the
 *   application "config" service (i.e., the merged configuration).
 * - Parameters type-hinted against array, but not named `$config` will
 *   be injected with an empty array.
 * - Scalar parameters will be resolved as null values.
 * - If a service cannot be found for a given typehint, the factory will
 *   raise an exception detailing this.
 * - Some services provided by Zend Framework components do not have
 *   entries based on their class name (for historical reasons); the
 *   factory contains a map of these class/interface names to the
 *   corresponding service name to allow them to resolve.
 *
 * `$options` passed to the factory are ignored in all cases, as we cannot
 * make assumptions about which argument(s) they might replace.
 */
class LazyControllerAbstractFactory implements AbstractFactoryInterface
{
    /**
     * Maps known classes/interfaces to the service that provides them; only
     * required for those services with no entry based on the class/interface
     * name.
     *
     * Extend the class if you wish to add to the list.
     *
     * @var string[]
     */
    protected $aliases = [
        ConsoleAdapterInterface::class  => 'ConsoleAdapter',
        FilterPluginManager::class      => 'FilterManager',
        HydratorPluginManager::class    => 'HydratorManager',
        InputFilterPluginManager::class => 'InputFilterManager',
        LogFilterManager::class         => 'LogFilterManager',
        LogFormatterManager::class      => 'LogFormatterManager',
        LogProcessorManager::class      => 'LogProcessorManager',
        LogWriterManager::class         => 'LogWriterManager',
        SerializerAdapterManager::class => 'SerializerAdapterManager',
        ValidatorPluginManager::class   => 'ValidatorManager',
    ];

    /**
     * {@inheritDoc}
     *
     * @return DispatchableInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $reflectionClass = new ReflectionClass($requestedName);

        if (null === ($constructor = $reflectionClass->getConstructor())) {
            return new $requestedName();
        }

        $reflectionParameters = $constructor->getParameters();

        if (empty($reflectionParameters)) {
            return new $requestedName();
        }

        $parameters = array_map(
            $this->resolveParameter($container->getServiceLocator(), $requestedName),
            $reflectionParameters
        );

        return new $requestedName(...$parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        if (! class_exists($requestedName)) {
            return false;
        }

        return in_array(DispatchableInterface::class, class_implements($requestedName), true);
    }

    /**
     * Resolve a parameter to a value.
     *
     * Returns a callback for resolving a parameter to a value.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @return callable
     */
    private function resolveParameter(ContainerInterface $container, $requestedName)
    {
        /**
         * @param ReflectionClass $parameter
         * @return mixed
         * @throws ServiceNotFoundException If type-hinted parameter cannot be
         *   resolved to a service in the container.
         */
        return function (ReflectionParameter $parameter) use ($container, $requestedName) {
            if ($parameter->isArray()
                && $parameter->getName() === 'config'
                && $container->has('config')
            ) {
                return $container->get('config');
            }

            if ($parameter->isArray()) {
                return [];
            }

            if (! $parameter->getClass()) {
                return;
            }

            $type = $parameter->getClass()->getName();
            $type = isset($this->aliases[$type]) ? $this->aliases[$type] : $type;

            if (! $container->has($type)) {
                throw new ServiceNotFoundException(sprintf(
                    'Unable to create controller "%s"; unable to resolve parameter "%s" using type hint "%s"',
                    $requestedName,
                    $parameter->getName(),
                    $type
                ));
            }

            return $container->get($type);
        };
    }

    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return bool
     * @SuppressWarnings("unused")
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return $this->canCreate($serviceLocator, $requestedName);
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return mixed
     * @SuppressWarnings("unused")
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return $this($serviceLocator, $requestedName);
    }
}
