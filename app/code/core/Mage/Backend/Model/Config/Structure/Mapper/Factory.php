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
 * @category    Mage
 * @package     Mage_Backend
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * System Configuration Mapper Factory
 */
class Mage_Backend_Model_Config_Structure_Mapper_Factory
{
    const MAPPER_SORTING                = 'sorting';
    const MAPPER_PATH                   = 'path';
    const MAPPER_IGNORE                 = 'ignore';
    const MAPPER_DEPENDENCIES           = 'dependencies';
    const MAPPER_ATTRIBUTE_INHERITANCE  = 'attribute_inheritance';

    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var array
     */
    protected $_typeMap = array(
        self::MAPPER_SORTING => 'Mage_Backend_Model_Config_Structure_Mapper_Sorting',
        self::MAPPER_PATH => 'Mage_Backend_Model_Config_Structure_Mapper_Path',
        self::MAPPER_IGNORE => 'Mage_Backend_Model_Config_Structure_Mapper_Ignore',
        self::MAPPER_DEPENDENCIES => 'Mage_Backend_Model_Config_Structure_Mapper_Dependencies',
        self::MAPPER_ATTRIBUTE_INHERITANCE => 'Mage_Backend_Model_Config_Structure_Mapper_Attribute_Inheritance',
    );

    public function __construct(Magento_ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Get mapper instance
     *
     * @param string $type
     * @param array $arguments
     * @return Mage_Backend_Model_Config_Structure_MapperInterface
     * @throws Exception
     */
    public function create($type, $arguments = array())
    {
        $className = $this->_getMapperClassNameByType($type);

        /** @var Mage_Backend_Model_Config_Structure_MapperInterface $mapperInstance  */
        $mapperInstance =  $this->_objectManager->get($className, $arguments);

        if (false == ($mapperInstance instanceof Mage_Backend_Model_Config_Structure_MapperInterface)) {
            throw new Exception('Mapper object is not instance on Mage_Backend_Model_Config_Structure_MapperInterface');
        }
        return $mapperInstance;
    }

    /**
     * Get mapper class name by type
     *
     * @param string $type
     * @return string mixed
     * @throws InvalidArgumentException
     */
    protected function _getMapperClassNameByType($type)
    {
        if (false == isset($this->_typeMap[$type])) {
            throw new InvalidArgumentException('Invalid mapper type: ' . $type);
        }
        return $this->_typeMap[$type];
    }
}
