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
 * @package     Mage_GiftMessage
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Gift Message model
 *
 * @method Mage_GiftMessage_Model_Resource_Message _getResource()
 * @method Mage_GiftMessage_Model_Resource_Message getResource()
 * @method int getCustomerId()
 * @method Mage_GiftMessage_Model_Message setCustomerId(int $value)
 * @method string getSender()
 * @method Mage_GiftMessage_Model_Message setSender(string $value)
 * @method string getRecipient()
 * @method Mage_GiftMessage_Model_Message setRecipient(string $value)
 * @method string getMessage()
 * @method Mage_GiftMessage_Model_Message setMessage(string $value)
 *
 * @category    Mage
 * @package     Mage_GiftMessage
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_GiftMessage_Model_Message extends Mage_Core_Model_Abstract
{
    /**
     * Allowed types of entities for using of gift messages
     *
     * @var array
     */
    static protected $_allowedEntityTypes = array(
        'order'         => 'Mage_Sales_Model_Order',
        'order_item'    => 'Mage_Sales_Model_Order_Item',
        'order_address' => 'Mage_Sales_Model_Order_Address',
        'quote'         => 'Mage_Sales_Model_Quote',
        'quote_item'    => 'Mage_Sales_Model_Quote_Item',
        'quote_address' => 'Mage_Sales_Model_Quote_Address',
        'quote_address_item' => 'Mage_Sales_Model_Quote_Address_Item'
    );

    protected function _construct()
    {
        $this->_init('Mage_GiftMessage_Model_Resource_Message');
    }

    /**
     * Return model from entity type
     *
     * @param string $type
     * @return Mage_Eav_Model_Entity_Abstract
     */
    public function getEntityModelByType($type)
    {
        $types = self::getAllowedEntityTypes();
        if(!isset($types[$type])) {
            Mage::throwException(Mage::helper('Mage_GiftMessage_Helper_Data')->__('Unknown entity type'));
        }

        return Mage::getModel($types[$type]);
    }

    /**
     * Checks thats gift message is empty
     *
     * @return boolean
     */
    public function isMessageEmpty()
    {
        return trim($this->getMessage()) == '';
    }

    /**
     * Return list of allowed entities for using in gift messages
     *
     * @return array
     */
    static public function getAllowedEntityTypes()
    {
        return self::$_allowedEntityTypes;
    }

}
