<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Fixture;

use Mtf\Fixture\InjectableFixture;

/**
 * Class TaxRate
 */
class TaxRate extends InjectableFixture
{
    /**
     * @var string
     */
    protected $repositoryClass = 'Magento\Tax\Test\Repository\TaxRate';

    /**
     * @var string
     */
    protected $handlerInterface = 'Magento\Tax\Test\Handler\TaxRate\TaxRateInterface';

    protected $defaultDataSet = [
        'code' => 'Tax Rate %isolation%',
        'rate' => '10',
        'tax_country_id' => 'United States',
        'tax_postcode' => '*',
        'tax_region_id' => 'California',
    ];

    protected $tax_calculation_rate_id = [
        'attribute_code' => 'tax_calculation_rate_id',
        'backend_type' => 'int',
        'is_required' => '1',
        'default_value' => '',
        'input' => '',
    ];

    protected $tax_country_id = [
        'attribute_code' => 'tax_country_id',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $tax_region_id = [
        'attribute_code' => 'tax_region_id',
        'backend_type' => 'int',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $tax_postcode = [
        'attribute_code' => 'tax_postcode',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $code = [
        'attribute_code' => 'code',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $rate = [
        'attribute_code' => 'rate',
        'backend_type' => 'decimal',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $zip_is_range = [
        'attribute_code' => 'zip_is_range',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $zip_from = [
        'attribute_code' => 'zip_from',
        'backend_type' => 'int',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $zip_to = [
        'attribute_code' => 'zip_to',
        'backend_type' => 'int',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $id = [
        'attribute_code' => 'id',
        'backend_type' => 'virtual',
    ];

    public function getTaxCalculationRateId()
    {
        return $this->getData('tax_calculation_rate_id');
    }

    public function getTaxCountryId()
    {
        return $this->getData('tax_country_id');
    }

    public function getTaxRegionId()
    {
        return $this->getData('tax_region_id');
    }

    public function getTaxPostcode()
    {
        return $this->getData('tax_postcode');
    }

    public function getCode()
    {
        return $this->getData('code');
    }

    public function getRate()
    {
        return $this->getData('rate');
    }

    public function getZipIsRange()
    {
        return $this->getData('zip_is_range');
    }

    public function getZipFrom()
    {
        return $this->getData('zip_from');
    }

    public function getZipTo()
    {
        return $this->getData('zip_to');
    }

    public function getId()
    {
        return $this->getData('id');
    }
}
