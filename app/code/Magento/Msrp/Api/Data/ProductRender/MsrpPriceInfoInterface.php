<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Msrp\Api\Data\ProductRender;

/**
 * Price interface.
 * @api
 */
interface MsrpPriceInfoInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @param string $msrpPrice
     * @return void
     */
    public function setMsrpPrice($msrpPrice);

    /**
     * @return string
     */
    public function getMsrpPrice();

    /**
     * @param string $isApplicable
     * @return void
     */
    public function setIsApplicable($isApplicable);

    /**
     * @return string
     */
    public function getIsApplicable();

    /**
     * @param string $isShownOnGesture
     * @return void
     */
    public function setIsShownPriceOnGesture($isShownOnGesture);

    /**
     * @return string
     */
    public function getIsShownPriceOnGesture();

    /**
     * @param string $msrpMessage
     * @return void
     */
    public function setMsrpMessage($msrpMessage);

    /**
     * @return string
     */
    public function getMsrpMessage();

    /**
     * @param string $explanationMessage
     * @return void
     */
    public function setExplanationMessage($explanationMessage);

    /**
     * @return string
     */
    public function getExplanationMessage();

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Msrp\Api\Data\ProductRender\MsrpPriceInfoExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Msrp\Api\Data\ProductRender\MsrpPriceInfoExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Msrp\Api\Data\ProductRender\MsrpPriceInfoExtensionInterface $extensionAttributes
    );
}
