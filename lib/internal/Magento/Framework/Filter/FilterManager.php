<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

use InvalidArgumentException;
use Laminas\Filter\FilterInterface;
use Magento\Framework\Filter\FilterManager\Config;
use Magento\Framework\ObjectManagerInterface;
use UnexpectedValueException;

/**
 * Magento Filter Manager
 *
 * @api
 * @method string email(string $value)
 * @method string money(string $value, $params = [])
 * @method string simple(string $value, $params = [])
 * @method string object(string $value, $params = [])
 * @method string sprintf(string $value, $params = [])
 * @method string template(string $value, $params = [])
 * @method string arrayFilter(string $value)
 * @method string removeAccents(string $value, $params = [])
 * @method string splitWords(string $value, $params = [])
 * @method string removeTags(string $value, $params = [])
 * @method string stripTags(string $value, $params = [])
 * @method string truncate(string $value, $params = [])
 * @method string truncateFilter(string $value, $params = [])
 * @method string encrypt(string $value, $params = [])
 * @method string decrypt(string $value, $params = [])
 * @method string translit(string $value)
 * @method string translitUrl(string $value)
 * @since 100.0.2
 */
class FilterManager
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var FactoryInterface[]
     */
    protected $factoryInstances;

    /**
     * @param ObjectManagerInterface $objectManger
     * @param Config $config
     */
    public function __construct(
        ObjectManagerInterface $objectManger,
        Config $config
    ) {
        $this->objectManager = $objectManger;
        $this->config = $config;
    }

    /**
     * Get filter object
     *
     * @param string $filterAlias
     * @param array $arguments
     * @return FilterInterface
     * @throws UnexpectedValueException
     */
    public function get($filterAlias, array $arguments = [])
    {
        $filter = $this->createFilterInstance($filterAlias, $arguments);
        if (!$filter instanceof FilterInterface) {
            throw new UnexpectedValueException(sprintf(
                'Filter object must implement %s interface, %s was given',
                FilterInterface::class,
                get_class($filter)
            ));
        }
        return $filter;
    }

    /**
     * Create filter instance
     *
     * @param string $filterAlias
     * @param array $arguments
     * @return FilterInterface
     * @throws InvalidArgumentException
     */
    protected function createFilterInstance($filterAlias, $arguments)
    {
        foreach ($this->getFilterFactories() as $factory) {
            if ($factory->canCreateFilter($filterAlias)) {
                return $factory->createFilter($filterAlias, $arguments);
            }
        }
        throw new InvalidArgumentException(sprintf(
            'Filter was not found by given alias %s',
            $filterAlias
        ));
    }

    /**
     * Get registered factories
     *
     * @return FactoryInterface[]
     * @throws UnexpectedValueException
     */
    protected function getFilterFactories()
    {
        if ($this->factoryInstances === null) {
            foreach ($this->config->getFactories() as $class) {
                $factory = $this->objectManager->create($class);
                if (!$factory instanceof FactoryInterface) {
                    throw new UnexpectedValueException(sprintf(
                        'Filter factory must implement %s interface, %s was given.',
                        FactoryInterface::class,
                        get_class($factory)
                    ));
                }
                $this->factoryInstances[] = $factory;
            }
        }
        return $this->factoryInstances;
    }

    /**
     * Create filter and filer value
     *
     * @param string $filterAlias
     * @param array $arguments
     * @return FilterInterface
     */
    public function __call($filterAlias, array $arguments = [])
    {
        $value = array_shift($arguments);
        if (count($arguments)) {
            $arguments = array_shift($arguments);
            if (!is_array($arguments)) {
                $arguments = [$arguments];
            }
        }
        return $this->createFilterInstance($filterAlias, $arguments)->filter($value);
    }
}
