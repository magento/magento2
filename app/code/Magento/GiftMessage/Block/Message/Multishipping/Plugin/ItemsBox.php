<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
     * @param \Magento\Framework\Object $addressEntity
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetItemsBoxTextAfter(
        \Magento\Multishipping\Block\Checkout\Shipping $subject,
        \Closure $proceed,
        \Magento\Framework\Object $addressEntity
    ) {
        $itemsBoxText = $proceed($addressEntity);
        return $itemsBoxText . $this->helper->getInline('multishipping_address', $addressEntity);
    }
}
