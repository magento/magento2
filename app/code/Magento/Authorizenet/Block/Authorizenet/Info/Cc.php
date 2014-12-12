<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Authorizenet\Block\Authorizenet\Info;

use Magento\Payment\Block\Info;

class Cc extends \Magento\Payment\Block\Info\Cc
{
    /**
     * Checkout progress information block flag
     *
     * @var bool
     */
    protected $_isCheckoutProgressBlockFlag = true;

    /**
     * @var string
     */
    protected $_template = 'Magento_Authorizenet::info/cc.phtml';

    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param \Magento\Core\Helper\Data $coreData
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Core\Helper\Data $coreData,
        array $data = []
    ) {
        $this->_coreData = $coreData;
        parent::__construct($context, $paymentConfig, $data);
    }

    /**
     * Render as PDF
     *
     * @return string
     */
    public function toPdf()
    {
        $this->setTemplate('Magento_Authorizenet::info/pdf.phtml');
        return $this->toHtml();
    }

    /**
     * Retrieve card info object
     *
     * @return \Magento\Payment\Model\Info
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
     * @return $this
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
        $cards = [];

        if (is_array($cardsData)) {
            foreach ($cardsData as $cardInfo) {
                $data = [];
                if ($cardInfo->getProcessedAmount()) {
                    $amount = $this->_coreData->currency($cardInfo->getProcessedAmount(), true, false);
                    $data[__('Processed Amount')] = $amount;
                }
                if ($cardInfo->getBalanceOnCard() && is_numeric($cardInfo->getBalanceOnCard())) {
                    $balance = $this->_coreData->currency($cardInfo->getBalanceOnCard(), true, false);
                    $data[__('Remaining Balance')] = $balance;
                }
                $cardInfo->setMethodInstance($this->getInfo()->getMethodInstance());
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
