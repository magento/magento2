<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GoogleShopping\Test\Fixture;

use Mtf\Fixture\InjectableFixture;

/**
 * Class GoogleShoppingAttribute
 * Google Shopping Attribute fixture
 *
 */
class GoogleShoppingAttribute extends InjectableFixture
{
    /**
     * @var string
     */
    protected $repositoryClass = 'Magento\GoogleShopping\Test\Repository\GoogleShoppingAttribute';

    // @codingStandardsIgnoreStart
    /**
     * @var string
     */
    protected $handlerInterface = 'Magento\GoogleShopping\Test\Handler\GoogleShoppingAttribute\GoogleShoppingAttributeInterface';
    // @codingStandardsIgnoreEnd

    protected $defaultDataSet = [
        'target_country' => 'United States',
        'attribute_set_id' => ['dataSet' => 'default'],
        'category' => 'Apparel & Accessories',
    ];

    protected $type_id = [
        'attribute_code' => 'type_id',
        'backend_type' => 'int',
        'is_required' => '1',
        'default_value' => '',
        'input' => '',
    ];

    protected $attribute_set_id = [
        'attribute_code' => 'attribute_set_id',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
        'source' => 'Magento\GoogleShopping\Test\Fixture\GoogleShoppingAttribute\AttributeSetId',
    ];

    protected $target_country = [
        'attribute_code' => 'target_country',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => 'US',
        'input' => '',
    ];

    protected $category = [
        'attribute_code' => 'category',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    public function getTypeId()
    {
        return $this->getData('type_id');
    }

    public function getAttributeSetId()
    {
        return $this->getData('attribute_set_id');
    }

    public function getTargetCountry()
    {
        return $this->getData('target_country');
    }

    public function getCategory()
    {
        return $this->getData('category');
    }
}
