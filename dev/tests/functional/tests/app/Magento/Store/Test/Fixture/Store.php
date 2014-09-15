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

namespace Magento\Store\Test\Fixture;

use Mtf\Fixture\InjectableFixture;

/**
 * Class Store
 * Store View fixture
 */
class Store extends InjectableFixture
{
    /**
     * @var string
     */
    protected $repositoryClass = 'Magento\Store\Test\Repository\Store';

    /**
     * @var string
     */
    protected $handlerInterface = 'Magento\Store\Test\Handler\Store\StoreInterface';

    protected $defaultDataSet = [
        'group_id' => ['dataSet' => 'default'],
        'name' => 'Custom_Store_%isolation%',
        'code' => 'code_%isolation%',
        'is_active' => 'Enabled',
    ];

    protected $store_id = [
        'attribute_code' => 'store_id',
        'backend_type' => 'smallint',
        'is_required' => '1',
        'default_value' => '',
        'input' => '',
    ];

    protected $code = [
        'attribute_code' => 'code',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => 'text',
    ];

    protected $website_id = [
        'attribute_code' => 'website_id',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $group_id = [
        'attribute_code' => 'group_id',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => 'select',
        'source' => 'Magento\Store\Test\Fixture\Store\GroupId',
    ];

    protected $name = [
        'attribute_code' => 'name',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => 'text',
    ];

    protected $sort_order = [
        'attribute_code' => 'sort_order',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => 'text',
    ];

    protected $is_active = [
        'attribute_code' => 'is_active',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => 'select',
    ];

    public function getStoreId()
    {
        return $this->getData('store_id');
    }

    public function getCode()
    {
        return $this->getData('code');
    }

    public function getWebsiteId()
    {
        return $this->getData('website_id');
    }

    public function getGroupId()
    {
        return $this->getData('group_id');
    }

    public function getName()
    {
        return $this->getData('name');
    }

    public function getSortOrder()
    {
        return $this->getData('sort_order');
    }

    public function getIsActive()
    {
        return $this->getData('is_active');
    }
}
