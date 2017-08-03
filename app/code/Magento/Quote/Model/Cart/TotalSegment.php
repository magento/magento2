<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Cart;

use Magento\Quote\Api\Data\TotalSegmentInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Extensible Cart Totals
 *
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class TotalSegment extends AbstractExtensibleModel implements TotalSegmentInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCode()
    {
        return $this->getData(self::CODE);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setCode($code)
    {
        return $this->setData(self::CODE, $code);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getValue()
    {
        return $this->getData(self::VALUE);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setTitle($title = null)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getArea()
    {
        return $this->getData(self::AREA);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setArea($area = null)
    {
        return $this->setData(self::AREA, $area);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Quote\Api\Data\TotalSegmentExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
