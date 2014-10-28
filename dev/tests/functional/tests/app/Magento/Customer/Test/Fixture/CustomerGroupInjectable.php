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
