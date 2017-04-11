<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\ResourceModel;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class RuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Magento/SalesRule/_files/rule_custom_product_attribute.php
     */
    public function testAfterSave()
    {
        $resource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\SalesRule\Model\ResourceModel\Rule::class
        );
        $items = $resource->getActiveAttributes();

        $this->assertEquals([['attribute_code' => 'attribute_for_sales_rule_1']], $items);
    }
}
