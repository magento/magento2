<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System Configuration Mapper Factory
 */
namespace Magento\Config\Model\Config\Structure\Mapper;

/**
 * @api
 * @since 2.0.0
 */
class Factory
{
    const MAPPER_SORTING = 'sorting';

    const MAPPER_PATH = 'path';

    const MAPPER_IGNORE = 'ignore';

    const MAPPER_DEPENDENCIES = 'dependencies';

    const MAPPER_ATTRIBUTE_INHERITANCE = 'attribute_inheritance';

    const MAPPER_EXTENDS = 'extends';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $_typeMap = [
        self::MAPPER_SORTING => \Magento\Config\Model\Config\Structure\Mapper\Sorting::class,
        self::MAPPER_PATH => \Magento\Config\Model\Config\Structure\Mapper\Path::class,
        self::MAPPER_IGNORE => \Magento\Config\Model\Config\Structure\Mapper\Ignore::class,
        self::MAPPER_DEPENDENCIES => \Magento\Config\Model\Config\Structure\Mapper\Dependencies::class,
        self::MAPPER_ATTRIBUTE_INHERITANCE =>
            \Magento\Config\Model\Config\Structure\Mapper\Attribute\Inheritance::class,
        self::MAPPER_EXTENDS => \Magento\Config\Model\Config\Structure\Mapper\ExtendsMapper::class,
    ];

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Get mapper instance
     *
     * @param string $type
     * @return \Magento\Config\Model\Config\Structure\MapperInterface
     * @throws \Exception
     * @since 2.0.0
     */
    public function create($type)
    {
        $className = $this->_getMapperClassNameByType($type);

        /** @var \Magento\Config\Model\Config\Structure\MapperInterface $mapperInstance  */
        $mapperInstance = $this->_objectManager->create($className);

        if (false == $mapperInstance instanceof \Magento\Config\Model\Config\Structure\MapperInterface) {
            throw new \Exception(
                'Mapper object is not instance on \Magento\Config\Model\Config\Structure\MapperInterface'
            );
        }
        return $mapperInstance;
    }

    /**
     * Get mapper class name by type
     *
     * @param string $type
     * @return string
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    protected function _getMapperClassNameByType($type)
    {
        if (false == isset($this->_typeMap[$type])) {
            throw new \InvalidArgumentException('Invalid mapper type: ' . $type);
        }
        return $this->_typeMap[$type];
    }
}
