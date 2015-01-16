<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Model\Attribute;

use Magento\TestFramework\Helper\Bootstrap;

class PriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\Gdata\Gshopping\Entry $entry
     * @dataProvider convertAttributeDataProvider
     */
    public function testConvertAttribute($product, $entry)
    {
        /** @var \Magento\GoogleShopping\Model\Attribute\Price $model */
        $model = Bootstrap::getObjectManager()->create('Magento\GoogleShopping\Model\Attribute\Price');
        $groupManagement = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Api\GroupManagementInterface'
        );
        $defaultCustomerGroup = $groupManagement->getDefaultGroup($product->getStoreId());
        $model->convertAttribute($product, $entry);
        $this->assertEquals($defaultCustomerGroup->getId(), $product->getCustomerGroupId());
    }

    /**
     * @return array
     */
    public function convertAttributeDataProvider()
    {
        $product = Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
        $entry = Bootstrap::getObjectManager()->create('Magento\Framework\Gdata\Gshopping\Entry');
        return [
            [$product, $entry]
        ];
    }
}
