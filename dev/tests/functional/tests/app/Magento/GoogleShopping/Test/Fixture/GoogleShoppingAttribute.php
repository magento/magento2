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
