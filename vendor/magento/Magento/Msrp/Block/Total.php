<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Msrp\Block;

/**
 * @method string getOriginalBlockName()
 */
class Total extends \Magento\Framework\View\Element\Template
{
    /** @var \Magento\Msrp\Model\Config */
    protected $config;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Msrp\Model\Config $config
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Msrp\Model\Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        /** @var \Magento\Checkout\Block\Cart\AbstractCart $originalBlock */
        $originalBlock = $this->getLayout()->getBlock($this->getOriginalBlockName());
        $quote = $originalBlock->getQuote();
        if (!$quote->hasCanApplyMsrp() && $this->config->isEnabled()) {
            $quote->collectTotals();
        }
        if ($quote->getCanApplyMsrp()) {
            $originalBlock->setTemplate('');
            return parent::_toHtml();
        } else {
            return '';
        }
    }
}
