<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Fixture;

use Mtf\Fixture\InjectableFixture;

/**
 * Class TaxRule
 */
class TaxRule extends InjectableFixture
{
    /**
     * @var string
     */
    protected $repositoryClass = 'Magento\Tax\Test\Repository\TaxRule';

    /**
     * @var string
     */
    protected $handlerInterface = 'Magento\Tax\Test\Handler\TaxRule\TaxRuleInterface';

    protected $defaultDataSet = [
        'code' => 'TaxIdentifier%isolation%',
        'tax_rate' => [
            'dataSet' => [
                'US-CA-*-Rate 1',
            ],
        ],
    ];

    protected $tax_calculation_rule_id = [
        'attribute_code' => 'tax_calculation_rule_id',
        'backend_type' => 'int',
        'is_required' => '1',
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

    protected $priority = [
        'attribute_code' => 'priority',
        'backend_type' => 'int',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $position = [
        'attribute_code' => 'position',
        'backend_type' => 'int',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $tax_rate = [
        'attribute_code' => 'tax_rate',
        'backend_type' => 'virtual',
        'source' => 'Magento\Tax\Test\Fixture\TaxRule\TaxRate',
    ];

    protected $tax_customer_class = [
        'attribute_code' => 'tax_customer_class',
        'backend_type' => 'virtual',
        'source' => 'Magento\Tax\Test\Fixture\TaxRule\TaxClass',
    ];

    protected $tax_product_class = [
        'attribute_code' => 'tax_product_class',
        'backend_type' => 'virtual',
        'source' => 'Magento\Tax\Test\Fixture\TaxRule\TaxClass',
    ];

    public function getTaxCalculationRuleId()
    {
        return $this->getData('tax_calculation_rule_id');
    }

    public function getCode()
    {
        return $this->getData('code');
    }

    public function getPriority()
    {
        return $this->getData('priority');
    }

    public function getPosition()
    {
        return $this->getData('position');
    }

    public function getTaxRate()
    {
        return $this->getData('tax_rate');
    }

    public function getTaxCustomerClass()
    {
        return $this->getData('tax_customer_class');
    }

    public function getTaxProductClass()
    {
        return $this->getData('tax_product_class');
    }
}
