<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\TaxDetails;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Tax\Api\Data\AppliedTaxRateInterface;

/**
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class AppliedTaxRate extends AbstractExtensibleModel implements AppliedTaxRateInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_CODE    = 'code';
    const KEY_TITLE   = 'title';
    const KEY_PERCENT = 'percent';
    /**#@-*/

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCode()
    {
        return $this->getData(self::KEY_CODE);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getTitle()
    {
        return $this->getData(self::KEY_TITLE);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getPercent()
    {
        return $this->getData(self::KEY_PERCENT);
    }

    /**
     * Set code
     *
     * @param string $code
     * @return $this
     * @since 2.0.0
     */
    public function setCode($code)
    {
        return $this->setData(self::KEY_CODE, $code);
    }

    /**
     * Set Title
     *
     * @param string $title
     * @return $this
     * @since 2.0.0
     */
    public function setTitle($title)
    {
        return $this->setData(self::KEY_TITLE, $title);
    }

    /**
     * Set Tax Percent
     *
     * @param float $percent
     * @return $this
     * @since 2.0.0
     */
    public function setPercent($percent)
    {
        return $this->setData(self::KEY_PERCENT, $percent);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Tax\Api\Data\AppliedTaxRateExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Tax\Api\Data\AppliedTaxRateExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\AppliedTaxRateExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
