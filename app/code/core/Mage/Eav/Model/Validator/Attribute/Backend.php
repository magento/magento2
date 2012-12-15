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
 * @package     Mage_Eav
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Validation EAV entity via EAV attributes' backend models
 *
 * @category   Mage
 * @package    Mage_Eav
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Eav_Model_Validator_Attribute_Backend extends Magento_Validator_ValidatorAbstract
{
    /**
     * @var array
     */
    protected $_messages;

    /**
     * Returns true if and only if $value meets the validation requirements.
     *
     * @param Mage_Core_Model_Abstract $entity
     * @return boolean
     * @throws InvalidArgumentException
     */
    public function isValid($entity)
    {
        $this->_messages = array();
        if (!($entity instanceof Mage_Core_Model_Abstract)) {
            throw new InvalidArgumentException('Model must be extended from Mage_Core_Model_Abstract');
        }
        /** @var Mage_Eav_Model_Entity_Abstract $resource */
        $resource = $entity->getResource();
        if (!($resource instanceof Mage_Eav_Model_Entity_Abstract)) {
            throw new InvalidArgumentException('Model resource must be extended from Mage_Eav_Model_Entity_Abstract');
        }
        $resource->loadAllAttributes($entity);
        $attributes = $resource->getAttributesByCode();
        /** @var Mage_Eav_Model_Entity_Attribute $attribute */
        foreach ($attributes as $attribute) {
            $backend = $attribute->getBackend();
            if (!method_exists($backend, 'validate')) {
                continue;
            }
            try {
                $result = $backend->validate($entity);
                if (false === $result) {
                    $this->_messages[$attribute->getAttributeCode()][] =
                        Mage::helper('Mage_Eav_Helper_Data')->__('The value of attribute "%s" is invalid',
                            $attribute->getAttributeCode());
                } elseif (is_string($result)) {
                    $this->_messages[$attribute->getAttributeCode()][] = $result;
                }
            } catch (Mage_Core_Exception $e) {
                $this->_messages[$attribute->getAttributeCode()][] = $e->getMessage();
            }
        }
        return 0 == count($this->_messages);
    }

    /**
     * Returns an array of messages that explain why the most recent isValid() call returned false.
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }
}
