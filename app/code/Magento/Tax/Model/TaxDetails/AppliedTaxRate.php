<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\TaxDetails;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Tax\Api\Data\AppliedTaxRateInterface;

/**
 * @codeCoverageIgnore
 */
class AppliedTaxRate extends AbstractExtensibleModel implements AppliedTaxRateInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->getData(AppliedTaxRateInterface::KEY_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->getData(AppliedTaxRateInterface::KEY_TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function getPercent()
    {
        return $this->getData(AppliedTaxRateInterface::KEY_PERCENT);
    }

    /**
     * Set code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        return $this->setData(AppliedTaxRateInterface::KEY_CODE, $code);
    }

    /**
     * Set Title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        return $this->setData(AppliedTaxRateInterface::KEY_TITLE, $title);
    }

    /**
     * Set Tax Percent
     *
     * @param float $percent
     * @return $this
     */
    public function setPercent($percent)
    {
        return $this->setData(AppliedTaxRateInterface::KEY_PERCENT, $percent);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Tax\Api\Data\AppliedTaxRateExtensionInterface|null
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
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\AppliedTaxRateExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
