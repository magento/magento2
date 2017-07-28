<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block;

/**
 * "Checkout" link
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 2.0.0
 */
class Link extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * @var \Magento\Framework\Module\Manager
     * @since 2.0.0
     */
    protected $_moduleManager;

    /**
     * @var \Magento\Checkout\Helper\Data
     * @since 2.0.0
     */
    protected $_checkoutHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param array $data
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        array $data = []
    ) {
        $this->_checkoutHelper = $checkoutHelper;
        parent::__construct($context, $data);
        $this->_moduleManager = $moduleManager;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getHref()
    {
        return $this->getUrl('checkout', ['_secure' => true]);
    }

    /**
     * Render block HTML
     *
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        if (!$this->_checkoutHelper->canOnepageCheckout() || !$this->_moduleManager->isOutputEnabled(
            'Magento_Checkout'
        )
        ) {
            return '';
        }
        return parent::_toHtml();
    }
}
