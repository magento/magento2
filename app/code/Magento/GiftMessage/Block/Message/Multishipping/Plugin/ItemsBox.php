<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Block\Message\Multishipping\Plugin;

/**
 * Multishipping items box plugin
 */
class ItemsBox
{
    /**
     * Gift message helper
     *
     * @var \Magento\GiftMessage\Helper\Message
     */
    protected $helper;

    /**
     * Construct
     *
     * @param \Magento\GiftMessage\Helper\Message $helper
     */
    public function __construct(\Magento\GiftMessage\Helper\Message $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Get items box message text for multishipping
     *
     * @param \Magento\Multishipping\Block\Checkout\Shipping $subject
     * @param callable $proceed
     * @param \Magento\Framework\DataObject $addressEntity
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetItemsBoxTextAfter(
        \Magento\Multishipping\Block\Checkout\Shipping $subject,
        \Closure $proceed,
        \Magento\Framework\DataObject $addressEntity
    ) {
        $itemsBoxText = $proceed($addressEntity);
        return $itemsBoxText . $this->helper->getInline('multishipping_address', $addressEntity);
    }
}
