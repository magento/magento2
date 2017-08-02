<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Msrp\Model\ProductRender;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Msrp\Api\Data\ProductRender\MsrpPriceInfoInterface;

/**
 * Class \Magento\Msrp\Model\ProductRender\MsrpPriceInfo
 *
 * @since 2.2.0
 */
class MsrpPriceInfo extends \Magento\Framework\Model\AbstractExtensibleModel implements
    MsrpPriceInfoInterface
{
    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setMsrpPrice($msrpPrice)
    {
        $this->setData('msrp_price', $msrpPrice);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getMsrpPrice()
    {
        return $this->getData('msrp_price');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setIsApplicable($isApplicable)
    {
        $this->setData('is_applicable', $isApplicable);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getIsApplicable()
    {
        return $this->getData('is_applicable');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setIsShownPriceOnGesture($isShownOnGesture)
    {
        $this->setData('is_shown_on_guesture', $isShownOnGesture);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getIsShownPriceOnGesture()
    {
        return $this->getData('is_shown_on_guesture');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setMsrpMessage($msrpMessage)
    {
        $this->setData('msrp_message', $msrpMessage);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getMsrpMessage()
    {
        return $this->getData('msrp_message');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setExplanationMessage($explanationMessage)
    {
        $this->setData('explanation_message', $explanationMessage);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getExplanationMessage()
    {
        return $this->getData('explanation_message');
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getExtensionAttributes()
    {
        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function setExtensionAttributes(
        \Magento\Msrp\Api\Data\ProductRender\MsrpPriceInfoExtensionInterface $extensionAttributes
    ) {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
