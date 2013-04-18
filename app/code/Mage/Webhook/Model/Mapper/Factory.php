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
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Factory for initializing mapper factories for specified data mapping.
 */
class Mage_Webhook_Model_Mapper_Factory
{
    const XML_PATH_MAPPINGS = 'global/webhook/mappings/';

    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @param Magento_ObjectManager $objectManager
     */
    public function __construct(Magento_ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Get mapper factory for specified mapping
     *
     * @param string $mapping
     * @param Mage_Core_Model_Config_Element $config
     * @return Mage_Webhook_Model_Mapper_Factory_Interface
     * @throws LogicException
     */
    public function getMapperFactory($mapping, Mage_Core_Model_Config_Element $config)
    {
        $options = $config->asArray();
        if (empty($options[$mapping]['mapper_factory'])) {
            throw new LogicException("Wrong mapping name $mapping.");
        }

        $factory = $this->_objectManager->create($options[$mapping]['mapper_factory'], array($this->_objectManager));
        if (!$factory instanceof Mage_Webhook_Model_Mapper_Factory_Interface) {
            throw new LogicException("Wrong Mapper type for mapping $mapping.");
        }

        return $factory;
    }
}
