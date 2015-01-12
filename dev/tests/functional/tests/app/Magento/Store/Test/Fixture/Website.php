<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Fixture;

use Mtf\Fixture\InjectableFixture;

/**
 * Class Website
 */
class Website extends InjectableFixture
{
    /**
     * @var string
     */
    protected $repositoryClass = 'Magento\Store\Test\Repository\Website';

    /**
     * @var string
     */
    protected $handlerInterface = 'Magento\Store\Test\Handler\Website\WebsiteInterface';

    protected $defaultDataSet = [
        'name' => 'Main Website',
        'code' => 'base',
        'website_id' => '1',
    ];

    protected $website_id = [
        'attribute_code' => 'website_id',
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
        'input' => '',
    ];

    protected $name = [
        'attribute_code' => 'name',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $sort_order = [
        'attribute_code' => 'sort_order',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $default_group_id = [
        'attribute_code' => 'default_group_id',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $is_default = [
        'attribute_code' => 'is_default',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    public function getWebsiteId()
    {
        return $this->getData('website_id');
    }

    public function getCode()
    {
        return $this->getData('code');
    }

    public function getName()
    {
        return $this->getData('name');
    }

    public function getSortOrder()
    {
        return $this->getData('sort_order');
    }

    public function getDefaultGroupId()
    {
        return $this->getData('default_group_id');
    }

    public function getIsDefault()
    {
        return $this->getData('is_default');
    }
}
