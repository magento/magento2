<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorization\Model\ResourceModel\Role\Grid;

/**
 * @magentoAppArea adminhtml
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Authorization\Model\ResourceModel\Role\Grid\Collection
     */
    private $_collection;

    protected function setUp()
    {
        $this->_collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Authorization\Model\ResourceModel\Role\Grid\Collection::class
        );
    }

    public function testGetItems()
    {
        $expectedResult = [
            [
                'role_type' => \Magento\Authorization\Model\Acl\Role\Group::ROLE_TYPE,
                'role_name' => \Magento\TestFramework\Bootstrap::ADMIN_ROLE_NAME,
            ],
        ];
        $actualResult = [];
        /** @var \Magento\Reports\Model\Item $reportItem */
        foreach ($this->_collection->getItems() as $reportItem) {
            $actualResult[] = array_intersect_key($reportItem->getData(), $expectedResult[0]);
        }
        $this->assertEquals($expectedResult, $actualResult);
    }
}
