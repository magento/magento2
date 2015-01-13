<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
        'name' => 'Default Store View',
        'code' => 'default',
        'is_active' => 'Enabled',
        'store_id' => 1,
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
