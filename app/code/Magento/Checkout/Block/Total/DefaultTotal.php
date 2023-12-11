<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Total;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\ConfigInterface;
use Magento\Checkout\Helper\Data as CheckoutHelper;

/**
 * Default Total Row Renderer
 */
class DefaultTotal extends \Magento\Checkout\Block\Cart\Totals
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Checkout::total/default.phtml';

    /**
     * @var \Magento\Store\Model\Store
     */
    protected $_store;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param ConfigInterface $salesConfig
     * @param array $layoutProcessors
     * @param array $data
     * @param CheckoutHelper $checkoutHelper
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        ConfigInterface $salesConfig,
        array $layoutProcessors = [],
        array $data = [],
        ?CheckoutHelper $checkoutHelper = null
    ) {
        $data['checkoutHelper'] = $checkoutHelper ?? ObjectManager::getInstance()->get(CheckoutHelper::class);
        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $salesConfig,
            $layoutProcessors,
            $data
        );
        $this->_store = $this->_storeManager->getStore();
    }

    /**
     * Get style assigned to total object
     *
     * @return string
     */
    public function getStyle()
    {
        return $this->getTotal()->getStyle();
    }

    /**
     * Set Total value.
     *
     * @param float $total
     * @return $this
     */
    public function setTotal($total)
    {
        $this->setData('total', $total);
        if ($total->getAddress()) {
            $this->_store = $total->getAddress()->getQuote()->getStore();
        }
        return $this;
    }

    /**
     * Return store.
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_store;
    }
}
