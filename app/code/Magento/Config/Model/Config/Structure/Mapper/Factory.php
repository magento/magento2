<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System Configuration Mapper Factory
 */
namespace Magento\Config\Model\Config\Structure\Mapper;

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
     */
    protected $_objectManager;

    /**
     * @var array
     */
    protected $_typeMap = [
        self::MAPPER_SORTING => 'Magento\Config\Model\Config\Structure\Mapper\Sorting',
        self::MAPPER_PATH => 'Magento\Config\Model\Config\Structure\Mapper\Path',
        self::MAPPER_IGNORE => 'Magento\Config\Model\Config\Structure\Mapper\Ignore',
        self::MAPPER_DEPENDENCIES => 'Magento\Config\Model\Config\Structure\Mapper\Dependencies',
        self::MAPPER_ATTRIBUTE_INHERITANCE => 'Magento\Config\Model\Config\Structure\Mapper\Attribute\Inheritance',
        self::MAPPER_EXTENDS => 'Magento\Config\Model\Config\Structure\Mapper\ExtendsMapper',
    ];

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
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
     */
    protected function _getMapperClassNameByType($type)
    {
        if (false == isset($this->_typeMap[$type])) {
            throw new \InvalidArgumentException('Invalid mapper type: ' . $type);
        }
        return $this->_typeMap[$type];
    }
}
