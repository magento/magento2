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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Paygate_Model_Authorizenet_Cards
{
    const CARDS_NAMESPACE = 'authorize_cards';
    const CARD_ID_KEY = 'id';
    const CARD_PROCESSED_AMOUNT_KEY = 'processed_amount';
    const CARD_CAPTURED_AMOUNT_KEY = 'captured_amount';
    const CARD_REFUNDED_AMOUNT_KEY = 'refunded_amount';

    /**
     * Cards information
     *
     * @var mixed
     */
    protected $_cards = array();

    /**
     * Payment instance
     *
     * @var Mage_Payment_Model_Info
     */
    protected $_payment = null;

    /**
     * Set payment instance for storing credit card information and partial authorizations
     *
     * @param Mage_Payment_Model_Info $payment
     * @return Mage_Paygate_Model_Authorizenet_Cards
     */
    public function setPayment(Mage_Payment_Model_Info $payment)
    {
        $this->_payment = $payment;
        $paymentCardsInformation = $this->_payment->getAdditionalInformation(self::CARDS_NAMESPACE);
        if ($paymentCardsInformation) {
            $this->_cards = $paymentCardsInformation;
        }

        return $this;
    }

    /**
     * Add based on $cardInfo card to payment and return Id of new item
     *
     * @param mixed $cardInfo
     * @return string
     */
    public function registerCard($cardInfo = array())
    {
        $this->_isPaymentValid();
        $cardId = md5(microtime(1));
        $cardInfo[self::CARD_ID_KEY] = $cardId;
        $this->_cards[$cardId] = $cardInfo;
        $this->_payment->setAdditionalInformation(self::CARDS_NAMESPACE, $this->_cards);
        return $this->getCard($cardId);
    }

    /**
     * Save data from card object in cards storage
     *
     * @param Varien_Object $card
     * @return Mage_Paygate_Model_Authorizenet_Cards
     */
    public function updateCard($card)
    {
        $cardId = $card->getData(self::CARD_ID_KEY);
        if ($cardId && isset($this->_cards[$cardId])) {
            $this->_cards[$cardId] = $card->getData();
            $this->_payment->setAdditionalInformation(self::CARDS_NAMESPACE, $this->_cards);
        }
        return $this;
    }

    /**
     * Retrieve card by ID
     *
     * @param string $cardId
     * @return Varien_Object|bool
     */
    public function getCard($cardId)
    {
        if (isset($this->_cards[$cardId])) {
            $card = new Varien_Object($this->_cards[$cardId]);
            return $card;
        }
        return false;
    }

    /**
     * Get all stored cards
     *
     * @return array
     */
    public function getCards()
    {
        $this->_isPaymentValid();
        $_cards = array();
        foreach(array_keys($this->_cards) as $key) {
            $_cards[$key] = $this->getCard($key);
        }
        return $_cards;
    }

    /**
     * Return count of saved cards
     *
     * @return int
     */
    public function getCardsCount()
    {
        $this->_isPaymentValid();
        return count($this->_cards);
    }

    /**
     * Return processed amount for all cards
     *
     * @return float
     */
    public function getProcessedAmount()
    {
        return $this->_getAmount(self::CARD_PROCESSED_AMOUNT_KEY);
    }

    /**
     * Return captured amount for all cards
     *
     * @return float
     */
    public function getCapturedAmount()
    {
        return $this->_getAmount(self::CARD_CAPTURED_AMOUNT_KEY);
    }

    /**
     * Return refunded amount for all cards
     *
     * @return float
     */
    public function getRefundedAmount()
    {
        return $this->_getAmount(self::CARD_REFUNDED_AMOUNT_KEY);
    }

    /**
     * Remove all cards from payment instance
     *
     * @return Mage_Paygate_Model_Authorizenet_Cart
     */
    public function flushCards()
    {
        $this->_cards = array();
        $this->_payment->setAdditionalInformation(self::CARDS_NAMESPACE, null);
        return $this;
    }

    /**
     * Check for payment instace present
     *
     * @throws Exception
     */
    protected function _isPaymentValid()
    {
        if (!$this->_payment) {
            throw new Exception('Payment instance is not set');
        }
    }
    /**
     * Return total for cards data fields
     *
     * $param string $key
     * @return float
     */
    public function _getAmount($key)
    {
        $amount = 0;
        foreach ($this->_cards as $card) {
            if (isset($card[$key])) {
                $amount += $card[$key];
            }
        }
        return $amount;
    }
}
