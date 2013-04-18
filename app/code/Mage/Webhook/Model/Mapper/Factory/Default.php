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
 * Default mapper factory which converts all Varien_Objects to array
 */
class Mage_Webhook_Model_Mapper_Factory_Default implements Mage_Webhook_Model_Mapper_Factory_Interface
{
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
     * (non-PHPdoc)
     * @see Mage_Webhook_Model_Mapper_Factory_Interface::getMapper()
     */
    public function getMapper($topic, array $data, Mage_Core_Model_Config_Element $config)
    {
        $customTopicMapperName = (string) $config->descend("topics/$topic/options/model");
        if (!empty($customTopicMapperName)) {
            $mapper = $this->_createMapper($customTopicMapperName, $topic, $data);
            if ($mapper) {
                return $mapper;
            }
        }

        $defaultMapperName = (string) $config->descend("default_mapper");
        if (!empty($defaultMapperName)) {
            $mapper = $this->_createMapper($defaultMapperName, $topic, $data);
            if ($mapper) {
                return $mapper;
            }
        }

        return $this->_createMapper('Mage_Webhook_Model_Mapper_Default_Factory', $topic, $data);
    }

    protected function _createMapper($className, $topic, array $data)
    {
        $model = $this->_objectManager->create($className, array($this->_objectManager));
        if (!$model instanceof Mage_Webhook_Model_Mapper_Default_Factory_Interface) {
            return false;
        }

        return $model->getMapper($topic, $data);
    }
}