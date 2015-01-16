<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Fixture;

use Mtf\Fixture\InjectableFixture;

/**
 * Class StoreGroup
 */
class StoreGroup extends InjectableFixture
{
    /**
     * @var string
     */
    protected $repositoryClass = 'Magento\Store\Test\Repository\StoreGroup';

    /**
     * @var string
     */
    protected $handlerInterface = 'Magento\Store\Test\Handler\StoreGroup\StoreGroupInterface';

    protected $defaultDataSet = [
        'website_id' => [
            'dataSet' => 'main_website',
        ],
        'name' => 'StoreGroup%isolation%',
        'root_category_id' => [
            'dataSet' => 'default_category',
        ],
    ];

    protected $group_id = [
        'attribute_code' => 'group_id',
        'backend_type' => 'smallint',
        'is_required' => '1',
        'default_value' => '',
        'input' => '',
    ];

    protected $website_id = [
        'attribute_code' => 'website_id',
        'backend_type' => 'virtual',
        'source' => 'Magento\Store\Test\Fixture\StoreGroup\WebsiteId',
    ];

    protected $name = [
        'attribute_code' => 'name',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $root_category_id = [
        'attribute_code' => 'root_category_id',
        'backend_type' => 'virtual',
        'source' => 'Magento\Store\Test\Fixture\StoreGroup\CategoryId',
    ];

    protected $default_store_id = [
        'attribute_code' => 'default_store_id',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    public function getGroupId()
    {
        return $this->getData('group_id');
    }

    public function getWebsiteId()
    {
        return $this->getData('website_id');
    }

    public function getName()
    {
        return $this->getData('name');
    }

    public function getRootCategoryId()
    {
        return $this->getData('root_category_id');
    }

    public function getDefaultStoreId()
    {
        return $this->getData('default_store_id');
    }
}
