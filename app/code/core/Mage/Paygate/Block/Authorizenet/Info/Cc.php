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
 * @package     Mage_Paygate
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Paygate_Block_Authorizenet_Info_Cc extends Mage_Payment_Block_Info_Cc
{
    /**
     * Checkout progress information block flag
     *
     * @var bool
     */
    protected $_isCheckoutProgressBlockFlag = true;
    /**
     * Set block template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Mage_Paygate::info/cc.phtml');
    }

    /**
     * Render as PDF
     *
     * @return string
     */
    public function toPdf()
    {
        $this->setTemplate('Mage_Paygate::info/pdf.phtml');
        return $this->toHtml();
    }

    /**
     * Retrieve card info object
     *
     * @return mixed
     */
    public function getInfo()
    {
        if ($this->hasCardInfoObject()) {
            return $this->getCardInfoObject();
        }
        return parent::getInfo();
    }

    /**
     * Set checkout progress information block flag
     * to avoid showing credit card information from payment quote
     * in Previously used card information block
     *
     * @param bool $flag
     * @return Mage_Paygate_Block_Authorizenet_Info_Cc
     */
    public function setCheckoutProgressBlock($flag)
    {
        $this->_isCheckoutProgressBlockFlag = $flag;
        return $this;
    }

    /**
     * Retrieve credit cards info
     *
     * @return array
     */
    public function getCards()
    {
        $cardsData = $this->getMethod()->getCardsStorage()->getCards();
        $cards = array();

        if (is_array($cardsData)) {
            foreach ($cardsData as $cardInfo) {
                $data = array();
                if ($cardInfo->getProcessedAmount()) {
                    $amount = Mage::helper('Mage_Core_Helper_Data')->currency($cardInfo->getProcessedAmount(), true, false);
                    $data[Mage::helper('Mage_Paygate_Helper_Data')->__('Processed Amount')] = $amount;
                }
                if ($cardInfo->getBalanceOnCard() && is_numeric($cardInfo->getBalanceOnCard())) {
                    $balance = Mage::helper('Mage_Core_Helper_Data')->currency($cardInfo->getBalanceOnCard(), true, false);
                    $data[Mage::helper('Mage_Paygate_Helper_Data')->__('Remaining Balance')] = $balance;
                }
                $this->setCardInfoObject($cardInfo);
                $cards[] = array_merge($this->getSpecificInformation(), $data);
                $this->unsCardInfoObject();
                $this->_paymentSpecificInformation = null;
            }
        }
        if ($this->getInfo()->getCcType() && $this->_isCheckoutProgressBlockFlag) {
            $cards[] = $this->getSpecificInformation();
        }
        return $cards;
    }
}
