<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

/**
 * Magento Filter Manager
 *
 * @method string email(string $value)
 * @method string money(string $value, $params = array())
 * @method string simple(string $value, $params = array())
 * @method string object(string $value, $params = array())
 * @method string sprintf(string $value, $params = array())
 * @method string template(string $value, $params = array())
 * @method string arrayFilter(string $value)
 * @method string removeAccents(string $value, $params = array())
 * @method string splitWords(string $value, $params = array())
 * @method string removeTags(string $value, $params = array())
 * @method string stripTags(string $value, $params = array())
 * @method string truncate(string $value, $params = array())
 * @method string encrypt(string $value, $params = array())
 * @method string decrypt(string $value, $params = array())
 * @method string translit(string $value)
 * @method string translitUrl(string $value)
 */
class FilterManager
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var FilterManager\Config
     */
    protected $config;

    /**
     * @var FactoryInterface[]
     */
    protected $factoryInstances;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManger
     * @param FilterManager\Config $config
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManger, FilterManager\Config $config)
    {
        $this->objectManager = $objectManger;
        $this->config = $config;
    }

    /**
     * Get filter object
     *
     * @param string $filterAlias
     * @param array $arguments
     * @return \Zend_Filter_Interface
     * @throws \UnexpectedValueException
     */
    public function get($filterAlias, array $arguments = [])
    {
        $filter = $this->createFilterInstance($filterAlias, $arguments);
        if (!$filter instanceof \Zend_Filter_Interface) {
            throw new \UnexpectedValueException(
                'Filter object must implement Zend_Filter_Interface interface, ' . get_class($filter) . ' was given.'
            );
        }
        return $filter;
    }

    /**
     * Create filter instance
     *
     * @param string $filterAlias
     * @param array $arguments
     * @return \Zend_Filter_Interface
     * @throws \InvalidArgumentException
     */
    protected function createFilterInstance($filterAlias, $arguments)
    {
        /** @var FactoryInterface $factory */
        foreach ($this->getFilterFactories() as $factory) {
            if ($factory->canCreateFilter($filterAlias)) {
                return $factory->createFilter($filterAlias, $arguments);
            }
        }
        throw new \InvalidArgumentException('Filter was not found by given alias ' . $filterAlias);
    }

    /**
     * Get registered factories
     *
     * @return FactoryInterface[]
     * @throws \UnexpectedValueException
     */
    protected function getFilterFactories()
    {
        if (null === $this->factoryInstances) {
            foreach ($this->config->getFactories() as $class) {
                $factory = $this->objectManager->create($class);
                if (!$factory instanceof FactoryInterface) {
                    throw new \UnexpectedValueException(
                        'Filter factory must implement FilterFactoryInterface interface, ' . get_class(
                            $factory
                        ) . ' was given.'
                    );
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
     * @return \Zend_Filter_Interface
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
