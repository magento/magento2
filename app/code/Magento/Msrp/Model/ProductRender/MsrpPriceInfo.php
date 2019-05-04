<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Msrp\Model\ProductRender;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Msrp\Api\Data\ProductRender\MsrpPriceInfoInterface;

class MsrpPriceInfo extends \Magento\Framework\Model\AbstractExtensibleModel implements
    MsrpPriceInfoInterface
{
    /**
     * @inheritdoc
     */
    public function setMsrpPrice($msrpPrice)
    {
        $this->setData('msrp_price', $msrpPrice);
    }

    /**
     * @inheritdoc
     */
    public function getMsrpPrice()
    {
        return $this->getData('msrp_price');
    }

    /**
     * @inheritdoc
     */
    public function setIsApplicable($isApplicable)
    {
        $this->setData('is_applicable', $isApplicable);
    }

    /**
     * @inheritdoc
     */
    public function getIsApplicable()
    {
        return $this->getData('is_applicable');
    }

    /**
     * @inheritdoc
     */
    public function setIsShownPriceOnGesture($isShownOnGesture)
    {
        $this->setData('is_shown_on_guesture', $isShownOnGesture);
    }

    /**
     * @inheritdoc
     */
    public function getIsShownPriceOnGesture()
    {
        return $this->getData('is_shown_on_guesture');
    }

    /**
     * @inheritdoc
     */
    public function setMsrpMessage($msrpMessage)
    {
        $this->setData('msrp_message', $msrpMessage);
    }

    /**
     * @inheritdoc
     */
    public function getMsrpMessage()
    {
        return $this->getData('msrp_message');
    }

    /**
     * @inheritdoc
     */
    public function setExplanationMessage($explanationMessage)
    {
        $this->setData('explanation_message', $explanationMessage);
    }

    /**
     * @inheritdoc
     */
    public function getExplanationMessage()
    {
        return $this->getData('explanation_message');
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes()
    {
        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(
        \Magento\Msrp\Api\Data\ProductRender\MsrpPriceInfoExtensionInterface $extensionAttributes
    ) {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
