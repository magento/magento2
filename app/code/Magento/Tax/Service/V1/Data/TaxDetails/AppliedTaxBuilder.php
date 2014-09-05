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
namespace Magento\Tax\Service\V1\Data\TaxDetails;

use Magento\Framework\Service\Data\AttributeValueBuilder;
use Magento\Framework\Service\Data\MetadataServiceInterface;

/**
 * Builder for the AppliedTax Service Data Object
 *
 * @method AppliedTax create()
 */
class AppliedTaxBuilder extends \Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder
{
    /**
     * AppliedTaxRate builder
     *
     * @var AppliedTaxRateBuilder
     */
    protected $appliedTaxRateBuilder;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Service\Data\ObjectFactory $objectFactory
     * @param AttributeValueBuilder $valueBuilder
     * @param MetadataServiceInterface $metadataService
     * @param AppliedTaxRateBuilder $appliedTaxRateBuilder
     */
    public function __construct(
        \Magento\Framework\Service\Data\ObjectFactory $objectFactory,
        AttributeValueBuilder $valueBuilder,
        MetadataServiceInterface $metadataService,
        AppliedTaxRateBuilder $appliedTaxRateBuilder
    ) {
        parent::__construct($objectFactory, $valueBuilder, $metadataService);
        $this->appliedTaxRateBuilder = $appliedTaxRateBuilder;
    }

    /**
     * Convenience method that returns AppliedTaxRateBuilder
     *
     * @return AppliedTaxRateBuilder
     */
    public function getAppliedTaxRateBuilder()
    {
        return $this->appliedTaxRateBuilder;
    }

    /**
     * Set tax rate key
     *
     * @param string $key
     * @return $this
     */
    public function setTaxRateKey($key)
    {
        return $this->_set(AppliedTax::KEY_TAX_RATE_KEY, $key);
    }

    /**
     * Set percent
     *
     * @param float $percent
     * @return $this
     */
    public function setPercent($percent)
    {
        return $this->_set(AppliedTax::KEY_PERCENT, $percent);
    }

    /**
     * Set amount
     *
     * @param float $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        return $this->_set(AppliedTax::KEY_AMOUNT, $amount);
    }

    /**
     * Set rates
     *
     * @param AppliedTaxRate[] $rates
     * @return $this
     */
    public function setRates($rates)
    {
        return $this->_set(AppliedTax::KEY_RATES, $rates);
    }

    /**
     * {@inheritdoc}
     */
    protected function _setDataValues(array $data)
    {
        if (array_key_exists(AppliedTax::KEY_RATES, $data)) {
            $rates = [];
            foreach ($data[AppliedTax::KEY_RATES] as $rateArray) {
                $rates[] = $this->appliedTaxRateBuilder->populateWithArray($rateArray)->create();
            }
            $data[AppliedTax::KEY_RATES] = $rates;
        }
        return parent::_setDataValues($data);
    }
}
