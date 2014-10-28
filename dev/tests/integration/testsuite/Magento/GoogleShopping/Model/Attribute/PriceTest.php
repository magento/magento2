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
        $customerGroupService = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Service\V1\CustomerGroupServiceInterface'
        );
        $defaultCustomerGroup = $customerGroupService->getDefaultGroup($product->getStoreId());
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
