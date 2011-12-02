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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Gift Card product options xml renderer
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Catalog_Product_Options_Giftcard extends Mage_XmlConnect_Block_Catalog_Product_Options
{
    /**
     * Get sender name
     *
     * @return string
     */
    public function getSenderName()
    {
        $senderName = $this->getDefaultValue('giftcard_sender_name');
        if (!strlen($senderName)) {
            $firstName = (string) Mage::getSingleton('Mage_Customer_Model_Session')->getCustomer()->getFirstname();
            $lastName  = (string) Mage::getSingleton('Mage_Customer_Model_Session')->getCustomer()->getLastname();

            if ($firstName && $lastName) {
                $senderName = $firstName . ' ' . $lastName;
            } else {
                $senderName = '';
            }
        }
        return $senderName;
    }

    /**
     * Get sender email
     *
     * @return string
     */
    public function getSenderEmail()
    {
        $senderEmail = $this->getDefaultValue('giftcard_sender_email');

        if (!strlen($senderEmail)) {
            $senderEmail = (string) Mage::getSingleton('Mage_Customer_Model_Session')->getCustomer()->getEmail();
        }
        return $senderEmail;
    }

    /**
     * Get pre-configured values from product
     *
     * @param  $value param id
     * @return string
     */
    protected function getDefaultValue($value)
    {
        if ($this->getProduct()) {
            return (string) $this->getProduct()->getPreconfiguredValues()->getData($value);
        } else {
            return '';
        }
    }

    /**
     * Check is message available for current product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool|int
     */
    public function isMessageAvailable(Mage_Catalog_Model_Product $product)
    {
        if ($product->getUseConfigAllowMessage()) {
            return Mage::getStoreConfigFlag(Enterprise_GiftCard_Model_Giftcard::XML_PATH_ALLOW_MESSAGE);
        } else {
            return (int) $product->getAllowMessage();
        }
    }

    /**
     * Check is email available for product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isEmailAvailable(Mage_Catalog_Model_Product $product)
    {
        if ($product->getTypeInstance()->isTypePhysical($product)) {
            return false;
        }
        return true;
    }

    /**
     * Is amount available for product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isAmountAvailable(Mage_Catalog_Model_Product $product)
    {
        if (!$product->getGiftcardAmounts()) {
            return false;
        }
        return true;
    }

    /**
     * Generate gift card product options xml
     *
     * @param Mage_Catalog_Model_Product $product
     * @param bool $isObject
     * @return string | Mage_XmlConnect_Model_Simplexml_Element
     */
    public function getProductOptionsXml(Mage_Catalog_Model_Product $product, $isObject = false)
    {
        /** set current product object */
        $this->setProduct($product);

        /** @var $xmlModel Mage_XmlConnect_Model_Simplexml_Element */
        $xmlModel = $this->getProductCustomOptionsXmlObject($product);

        /** @var $optionsXmlObj Mage_XmlConnect_Model_Simplexml_Element */
        $optionsXmlObj = $xmlModel->options;

        if (!$product->isSalable()) {
            return $isObject ? $xmlModel : $xmlModel->asNiceXml();
        }

        /** @var $priceModel Enterprise_GiftCard_Block_Catalog_Product_Price */
        $priceModel = $product->getPriceModel();

        /** @var $coreHelper Mage_Core_Helper_Data */
        $coreHelper = Mage::helper('Mage_Core_Helper_Data');

        $configValue = $this->getDefaultValue('giftcard_amount');

        /**
         * Render fixed amounts options
         */

        /** @var $fixedAmountsNode Mage_XmlConnect_Model_Simplexml_Element */
        $fixedAmountsNode = $optionsXmlObj->addChild('fixed_amounts');
        if ($this->isAmountAvailable($product)) {
            $amounts = $priceModel->getSortedAmounts($product);
            if (count($amounts)) {
                foreach ($amounts as $price) {
                    $amountNode = $fixedAmountsNode->addChild('amount');
                    if ($configValue == $price) {
                        $amountNode->addAttribute('selected', 1);
                    }
                    $amountNode->addAttribute('formatted_price', $xmlModel->xmlAttribute(
                        $coreHelper->currency($price, true, false)
                    ));
                    $amountNode->addAttribute('price', $price);
                }
            }
        }

        /**
         * Render open amount options
         */
        /** @var $openAmountNode Mage_XmlConnect_Model_Simplexml_Element */
        $openAmountNode = $optionsXmlObj->addChild('open_amount');
        if ($product->getAllowOpenAmount()) {
            $openAmountNode->addAttribute('enabled', 1);

            if ($configValue == 'custom') {
                $openAmountNode->addAttribute('selected_amount', $this->getDefaultValue('custom_giftcard_amount'));
            }
            if ($priceModel->getMinAmount($product)) {
                $minPrice  = $product->getOpenAmountMin();
                $minAmount = $coreHelper->currency($minPrice, true, false);
            } else {
                $minAmount = $minPrice = 0;
            }
            $openAmountNode->addAttribute('formatted_min_amount', $xmlModel->xmlAttribute($minAmount));
            $openAmountNode->addAttribute('min_amount', $minPrice);

            if ($priceModel->getMaxAmount($product)) {
                $maxPrice  = $product->getOpenAmountMax();
                $maxAmount = $coreHelper->currency($maxPrice, true, false);
            } else {
                $maxAmount = $maxPrice = 0;
            }
            $openAmountNode->addAttribute('formatted_max_amount', $xmlModel->xmlAttribute($maxAmount));
            $openAmountNode->addAttribute('max_amount', $maxPrice);
        } else {
            $openAmountNode->addAttribute('enabled', 0);
        }

        /**
         * Render Gift Card form options
         */
        $form = $optionsXmlObj->addCustomChild('form', null, array(
            'name'      => 'giftcard-send-form',
            'method'    => 'post'
        ));

        $senderFieldset = $form->addCustomChild('fieldset', null, array(
            'legend' => $this->__('Sender Information')
        ));

        $senderFieldset->addField('giftcard_sender_name', 'text', array(
            'label'     => Mage::helper('Enterprise_GiftCard_Helper_Data')->__('Sender Name'),
            'required'  => 'true',
            'value'     => $this->getSenderName()
        ));

        $recipientFieldset = $form->addCustomChild('fieldset', null, array(
            'legend' => $this->__('Recipient Information')
        ));

        $recipientFieldset->addField('giftcard_recipient_name', 'text', array(
            'label'     => Mage::helper('Enterprise_GiftCard_Helper_Data')->__('Recipient Name'),
            'required'  => 'true',
            'value'     => $this->getDefaultValue('giftcard_recipient_name')
        ));

        if ($this->isEmailAvailable($product)) {
            $senderFieldset->addField('giftcard_sender_email', 'email', array(
                'label'     => Mage::helper('Enterprise_GiftCard_Helper_Data')->__('Sender Email'),
                'required'  => 'true',
                'value'     => $this->getSenderEmail()
            ));

            $recipientFieldset->addField('giftcard_recipient_email', 'email', array(
                'label'     => Mage::helper('Enterprise_GiftCard_Helper_Data')->__('Recipient Email'),
                'required'  => 'true',
                'value'     => $this->getDefaultValue('giftcard_recipient_email')
            ));
        }

        if ($this->isMessageAvailable($product)) {
            $messageMaxLength = (int) Mage::getStoreConfig(
                Enterprise_GiftCard_Model_Giftcard::XML_PATH_MESSAGE_MAX_LENGTH
            );
            $recipientFieldset->addField('giftcard_message', 'textarea', array(
                'label'     => Mage::helper('Enterprise_GiftCard_Helper_Data')->__('Message'),
                'required'  => 'false',
                'max_length'=> $messageMaxLength,
                'value'     => $this->getDefaultValue('giftcard_message')
            ));
        }
        return $isObject ? $xmlModel : $xmlModel->asNiceXml();
    }
}
