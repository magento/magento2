<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block;

use Magento\Framework\View\Element\Template;

/**
 * @api
 * @since 2.0.0
 */
class QuoteShortcutButtons extends \Magento\Catalog\Block\ShortcutButtons
{
    /**
     * @var \Magento\Checkout\Model\Session
     * @since 2.0.0
     */
    protected $_checkoutSession;

    /**
     * @param Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param array $data
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct(
        Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        parent::__construct($context, false, null, $data);
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * Dispatch shortcuts container event
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _beforeToHtml()
    {
        $this->_eventManager->dispatch(
            'shortcut_buttons_container',
            [
                'container' => $this,
                'is_catalog_product' => $this->_isCatalogProduct,
                'or_position' => $this->_orPosition,
                'checkout_session' => $this->_checkoutSession
            ]
        );
        return $this;
    }
}
