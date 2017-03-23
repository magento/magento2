<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block;

/**
 * HSS iframe block
 */
class Iframe extends \Magento\Payment\Block\Form
{
    /**
     * Whether the block should be eventually rendered
     *
     * @var bool
     */
    protected $_shouldRender = false;

    /**
     * Order object
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_paymentMethodCode;

    /**
     * Current iframe block instance
     *
     * @var \Magento\Payment\Block\Form
     */
    protected $_block;

    /**
     * @var string
     */
    protected $_template = 'hss/js.phtml';

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Paypal\Helper\Hss
     */
    protected $_hssHelper;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    protected $readFactory;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $reader;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Paypal\Helper\Hss $hssHelper
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     * @param \Magento\Framework\Module\Dir\Reader $reader
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Paypal\Helper\Hss $hssHelper,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\Module\Dir\Reader $reader,
        array $data = []
    ) {
        $this->_hssHelper = $hssHelper;
        $this->_orderFactory = $orderFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_isScopePrivate = true;
        $this->readFactory = $readFactory;
        $this->reader = $reader;
        parent::__construct($context, $data);
    }

    /**
     * Internal constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $paymentCode = $this->_getCheckout()->getQuote()->getPayment()->getMethod();
        if (in_array($paymentCode, $this->_hssHelper->getHssMethods())) {
            $this->_paymentMethodCode = $paymentCode;
            $templatePath = str_replace('_', '', $paymentCode);
            $templateFile = "{$templatePath}/iframe.phtml";
            $directory = $this->readFactory->create($this->reader->getModuleDir('', 'Magento_Paypal'));
            $file = $this->resolver->getTemplateFileName($templateFile, ['module' => 'Magento_Paypal']);
            if ($file && $directory->isExist($directory->getRelativePath($file))) {
                $this->setTemplate($templateFile);
            } else {
                $this->setTemplate('hss/iframe.phtml');
            }
        }
    }

    /**
     * Get current block instance
     *
     * @return \Magento\Payment\Block\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getBlock()
    {
        if (!$this->_block) {
            $this->_block = $this->getLayout()->createBlock(
                'Magento\\Paypal\\Block\\' . str_replace(
                    ' ',
                    '\\',
                    ucwords(str_replace('_', ' ', $this->_paymentMethodCode))
                ) . '\\Iframe'
            );
            if (!$this->_block instanceof \Magento\Paypal\Block\Iframe) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid block type'));
            }
        }

        return $this->_block;
    }

    /**
     * Get order object
     *
     * @return \Magento\Sales\Model\Order
     */
    protected function _getOrder()
    {
        if (!$this->_order) {
            $incrementId = $this->_getCheckout()->getLastRealOrderId();
            $this->_order = $this->_orderFactory->create()->loadByIncrementId($incrementId);
        }
        return $this->_order;
    }

    /**
     * Get frontend checkout session object
     *
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckout()
    {
        return $this->_checkoutSession;
    }

    /**
     * Before rendering html, check if is block rendering needed
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _beforeToHtml()
    {
        if ($this->_getOrder()->getId() &&
            $this->_getOrder()->getQuoteId() == $this->_getCheckout()->getLastQuoteId() &&
            $this->_paymentMethodCode
        ) {
            $this->_shouldRender = true;
        }

        if ($this->getGotoSection() || $this->getGotoSuccessPage()) {
            $this->_shouldRender = true;
        }

        return parent::_beforeToHtml();
    }

    /**
     * Render the block if needed
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->_isAfterPaymentSave()) {
            $this->setTemplate('hss/js.phtml');
            return parent::_toHtml();
        }
        if (!$this->_shouldRender) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * Check whether block is rendering after save payment
     *
     * @return bool
     */
    protected function _isAfterPaymentSave()
    {
        $quote = $this->_getCheckout()->getQuote();
        if ($quote->getPayment()->getMethod() == $this->_paymentMethodCode &&
            $quote->getIsActive() &&
            $this->getTemplate() &&
            $this->getRequest()->getActionName() == 'savePayment'
        ) {
            return true;
        }

        return false;
    }

    /**
     * Get iframe action URL
     *
     * @return string
     */
    public function getFrameActionUrl()
    {
        return $this->_getBlock()->getFrameActionUrl();
    }

    /**
     * Get secure token
     *
     * @return string
     */
    public function getSecureToken()
    {
        return $this->_getBlock()->getSecureToken();
    }

    /**
     * Get secure token ID
     *
     * @return string
     */
    public function getSecureTokenId()
    {
        return $this->_getBlock()->getSecureTokenId();
    }

    /**
     * Get payflow transaction URL
     *
     * @return string
     */
    public function getTransactionUrl()
    {
        return $this->_getBlock()->getTransactionUrl();
    }

    /**
     * Check sandbox mode
     *
     * @return bool
     */
    public function isTestMode()
    {
        return $this->_getBlock()->isTestMode();
    }
}
