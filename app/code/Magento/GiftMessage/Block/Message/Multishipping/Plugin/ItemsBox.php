<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Block\Message\Multishipping\Plugin;

use Magento\Multishipping\Block\Checkout\Shipping as ShippingBlock;
use Magento\GiftMessage\Helper\Message as MessageHelper;
use Magento\Framework\DataObject;

/**
 * Multishipping items box plugin
 * @since 2.0.0
 */
class ItemsBox
{
    /**
     * Gift message helper
     *
     * @var MessageHelper
     * @since 2.0.0
     */
    protected $helper;

    /**
     * Construct
     *
     * @param MessageHelper $helper
     * @since 2.0.0
     */
    public function __construct(MessageHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Get items box message text for multishipping
     *
     * @param ShippingBlock $subject
     * @param string $itemsBoxText
     * @param DataObject $addressEntity
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function afterGetItemsBoxTextAfter(ShippingBlock $subject, $itemsBoxText, DataObject $addressEntity)
    {
        return $itemsBoxText . $this->helper->getInline('multishipping_address', $addressEntity);
    }
}
