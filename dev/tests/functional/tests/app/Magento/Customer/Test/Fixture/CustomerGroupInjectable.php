<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Fixture;

use Mtf\Fixture\InjectableFixture;

/**
 * Class CustomerGroupInjectable
 * CustomerGroupInjectable fixture
 */
class CustomerGroupInjectable extends InjectableFixture
{
    /**
     * @var string
     */
    protected $repositoryClass = 'Magento\Customer\Test\Repository\CustomerGroupInjectable';

    /**
     * @var string
     */
    // @codingStandardsIgnoreStart
    protected $handlerInterface = 'Magento\Customer\Test\Handler\CustomerGroupInjectable\CustomerGroupInjectableInterface';
    // @codingStandardsIgnoreEnd

    protected $defaultDataSet = [
        'customer_group_code' => 'customer_code_%isolation%',
        'tax_class_id' => ['dataSet' => 'customer_tax_class'],
    ];

    protected $customer_group_code = [
        'attribute_code' => 'code',
        'backend_type' => 'varchar',
        'is_required' => '1',
        'default_value' => '',
        'input' => 'text',
    ];

    protected $tax_class_id = [
        'attribute_code' => 'tax_class',
        'backend_type' => 'varchar',
        'is_required' => '1',
        'default_value' => '',
        'input' => 'select',
        'source' => 'Magento\Customer\Test\Fixture\CustomerGroup\TaxClassIds',
    ];

    protected $customer_group_id = [
        'attribute_code' => 'customer_group_id',
        'backend_type' => 'virtual',
    ];

    public function getCustomerGroupCode()
    {
        return $this->getData('customer_group_code');
    }

    public function getTaxClassId()
    {
        return $this->getData('tax_class_id');
    }

    public function getCustomerGroupId()
    {
        return $this->getData('customer_group_id');
    }
}
