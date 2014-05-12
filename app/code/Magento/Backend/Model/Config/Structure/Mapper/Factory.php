<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * System Configuration Mapper Factory
 */
namespace Magento\Backend\Model\Config\Structure\Mapper;

class Factory
{
    const MAPPER_SORTING = 'sorting';

    const MAPPER_PATH = 'path';

    const MAPPER_IGNORE = 'ignore';

    const MAPPER_DEPENDENCIES = 'dependencies';

    const MAPPER_ATTRIBUTE_INHERITANCE = 'attribute_inheritance';

    const MAPPER_EXTENDS = 'extends';

    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var array
     */
    protected $_typeMap = array(
        self::MAPPER_SORTING => 'Magento\Backend\Model\Config\Structure\Mapper\Sorting',
        self::MAPPER_PATH => 'Magento\Backend\Model\Config\Structure\Mapper\Path',
        self::MAPPER_IGNORE => 'Magento\Backend\Model\Config\Structure\Mapper\Ignore',
        self::MAPPER_DEPENDENCIES => 'Magento\Backend\Model\Config\Structure\Mapper\Dependencies',
        self::MAPPER_ATTRIBUTE_INHERITANCE => 'Magento\Backend\Model\Config\Structure\Mapper\Attribute\Inheritance',
        self::MAPPER_EXTENDS => 'Magento\Backend\Model\Config\Structure\Mapper\ExtendsMapper'
    );

    /**
     * @param \Magento\Framework\ObjectManager $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Get mapper instance
     *
     * @param string $type
     * @return \Magento\Backend\Model\Config\Structure\MapperInterface
     * @throws \Exception
     */
    public function create($type)
    {
        $className = $this->_getMapperClassNameByType($type);

        /** @var \Magento\Backend\Model\Config\Structure\MapperInterface $mapperInstance  */
        $mapperInstance = $this->_objectManager->create($className);

        if (false == $mapperInstance instanceof \Magento\Backend\Model\Config\Structure\MapperInterface) {
            throw new \Exception(
                'Mapper object is not instance on \Magento\Backend\Model\Config\Structure\MapperInterface'
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
