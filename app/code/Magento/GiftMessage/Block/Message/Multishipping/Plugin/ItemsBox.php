<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Block\Message\Multishipping\Plugin;

use Magento\Multishipping\Block\Checkout\Shipping;
use Magento\GiftMessage\Helper\Message as HelperMessage;
use Magento\Framework\DataObject;

/**
 * Multishipping items box plugin
 */
class ItemsBox
{
    /**
     * Gift message helper
     *
     * @var HelperMessage
     */
    protected $helper;

    /**
     * Construct
     *
     * @param HelperMessage $helper
     */
    public function __construct(HelperMessage $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Get items box message text for multishipping
     *
     * @param Shipping $subject
     * @param string $itemsBoxText
     * @param DataObject $addressEntity
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetItemsBoxTextAfter(Shipping $subject, $itemsBoxText, DataObject $addressEntity)
    {
        return $itemsBoxText . $this->helper->getInline('multishipping_address', $addressEntity);
    }
}
