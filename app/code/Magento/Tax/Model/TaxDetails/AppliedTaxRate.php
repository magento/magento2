<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
}
