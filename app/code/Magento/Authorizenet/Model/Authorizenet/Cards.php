<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Authorizenet\Model\Authorizenet;

class Cards
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
    protected $_cards = [];

    /**
     * Payment instance
     *
     * @var \Magento\Payment\Model\Info
     */
    protected $_payment = null;

    /**
     * Set payment instance for storing credit card information and partial authorizations
     *
     * @param \Magento\Payment\Model\Info $payment
     * @return $this
     */
    public function setPayment(\Magento\Payment\Model\Info $payment)
    {
        $this->_payment = $payment;
        $this->_initCards();
        return $this;
    }

    /**
     * Init cards data
     *
     * @return void
     */
    protected function _initCards()
    {
        $paymentCardsInformation = $this->_payment->getAdditionalInformation(self::CARDS_NAMESPACE);
        if ($paymentCardsInformation) {
            $additionalInfo = $this->_payment->getAdditionalInformation();
            unset($additionalInfo[self::CARDS_NAMESPACE]);

            foreach ($paymentCardsInformation as $cardId => $data) {
                $paymentCardsInformation[$cardId]['additional_information'] = $additionalInfo;
            }

            $this->_cards = $paymentCardsInformation;
        }
    }

    /**
     * Add based on $cardInfo card to payment and return Id of new item
     *
     * @param mixed $cardInfo
     * @return string
     */
    public function registerCard($cardInfo = [])
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
     * @param \Magento\Framework\Object $card
     * @return $this
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
     * @return \Magento\Framework\Object|false
     */
    public function getCard($cardId)
    {
        if (isset($this->_cards[$cardId])) {
            $card = new \Magento\Framework\Object($this->_cards[$cardId]);
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
        $_cards = [];
        foreach (array_keys($this->_cards) as $key) {
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
     * @return $this
     */
    public function flushCards()
    {
        $this->_cards = [];
        $this->_payment->setAdditionalInformation(self::CARDS_NAMESPACE, null);
        return $this;
    }

    /**
     * Check for payment instance present
     *
     * @return void
     * @throws \Exception
     */
    protected function _isPaymentValid()
    {
        if (!$this->_payment) {
            throw new \Exception('Payment instance is not set');
        }
    }

    /**
     * Return total for cards data fields
     *
     * @param string $key
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
