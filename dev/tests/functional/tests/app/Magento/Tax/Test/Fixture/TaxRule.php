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
                'US-CA-*-Rate 1'
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
