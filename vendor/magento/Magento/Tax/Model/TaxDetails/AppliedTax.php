<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Tax\Model\TaxDetails;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Tax\Api\Data\AppliedTaxInterface;

/**
 * @codeCoverageIgnore
 */
class AppliedTax extends AbstractExtensibleModel implements AppliedTaxInterface
{
    /**
     * {@inheritdoc}
     */
    public function getTaxRateKey()
    {
        return $this->getData(AppliedTaxInterface::KEY_TAX_RATE_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function getPercent()
    {
        return $this->getData(AppliedTaxInterface::KEY_PERCENT);
    }

    /**
     * {@inheritdoc}
     */
    public function getAmount()
    {
        return $this->getData(AppliedTaxInterface::KEY_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function getRates()
    {
        return $this->getData(AppliedTaxInterface::KEY_RATES);
    }
}
