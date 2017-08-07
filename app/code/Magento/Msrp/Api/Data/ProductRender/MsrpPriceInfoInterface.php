<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Msrp\Api\Data\ProductRender;

/**
 * Price interface.
 * @api
 * @since 2.2.0
 */
interface MsrpPriceInfoInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @param string $msrpPrice
     * @return void
     * @since 2.2.0
     */
    public function setMsrpPrice($msrpPrice);

    /**
     * @return string
     * @since 2.2.0
     */
    public function getMsrpPrice();

    /**
     * @param string $isApplicable
     * @return void
     * @since 2.2.0
     */
    public function setIsApplicable($isApplicable);

    /**
     * @return string
     * @since 2.2.0
     */
    public function getIsApplicable();

    /**
     * @param string $isShownOnGesture
     * @return void
     * @since 2.2.0
     */
    public function setIsShownPriceOnGesture($isShownOnGesture);

    /**
     * @return string
     * @since 2.2.0
     */
    public function getIsShownPriceOnGesture();

    /**
     * @param string $msrpMessage
     * @return void
     * @since 2.2.0
     */
    public function setMsrpMessage($msrpMessage);

    /**
     * @return string
     * @since 2.2.0
     */
    public function getMsrpMessage();

    /**
     * @param string $explanationMessage
     * @return void
     * @since 2.2.0
     */
    public function setExplanationMessage($explanationMessage);

    /**
     * @return string
     * @since 2.2.0
     */
    public function getExplanationMessage();

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Msrp\Api\Data\ProductRender\MsrpPriceInfoExtensionInterface|null
     * @since 2.2.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Msrp\Api\Data\ProductRender\MsrpPriceInfoExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.2.0
     */
    public function setExtensionAttributes(
        \Magento\Msrp\Api\Data\ProductRender\MsrpPriceInfoExtensionInterface $extensionAttributes
    );
}
