<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Msrp\Block;

/**
 * @api
 * @method string getOriginalBlockName()
 * @since 2.0.0
 */
class Total extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Msrp\Model\Config
     * @since 2.0.0
     */
    protected $config;

    /**
     * @var \Magento\Msrp\Model\Quote\Msrp
     * @since 2.0.0
     */
    protected $msrp;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Msrp\Model\Config $config
     * @param \Magento\Msrp\Model\Quote\Msrp $msrp
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Msrp\Model\Config $config,
        \Magento\Msrp\Model\Quote\Msrp $msrp,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->msrp = $msrp;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        /** @var \Magento\Checkout\Block\Cart\AbstractCart $originalBlock */
        $originalBlock = $this->getLayout()->getBlock($this->getOriginalBlockName());
        $quote = $originalBlock->getQuote();
        if (!$this->msrp->getCanApplyMsrp($quote->getId()) && $this->config->isEnabled()) {
            $quote->collectTotals();
        }
        if ($this->msrp->getCanApplyMsrp($quote->getId())) {
            $originalBlock->setTemplate('');
            return parent::_toHtml();
        } else {
            return '';
        }
    }
}
