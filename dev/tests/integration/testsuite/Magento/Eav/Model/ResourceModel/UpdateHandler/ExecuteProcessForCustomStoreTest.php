<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\ResourceModel\UpdateHandler;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\ResourceModel\UpdateHandlerAbstract;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Eav\Model\ResourceModel\UpdateHandler;

/**
 * @magentoAppArea adminhtml
 * @magentoAppIsolation enabled
 */
class ExecuteProcessForCustomStoreTest extends UpdateHandlerAbstract
{
    /**
     * @covers \Magento\Eav\Model\ResourceModel\UpdateHandler::execute
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @dataProvider getCustomStoreDataProvider
     * @param $code
     * @param $snapshotValue
     * @param $newValue
     * @param $expected
     * @magentoDbIsolation disabled
     */
    public function testExecuteProcessForCustomStore($code, $snapshotValue, $newValue, $expected)
    {
        $store = Bootstrap::getObjectManager()->create(\Magento\Store\Model\Store::class);
        $store->load('fixture_second_store', 'code');

        $this->reindexAll();

        if ($snapshotValue !== '-') {
            $entity = Bootstrap::getObjectManager()->create(Product::class);
            $entity->setStoreId($store->getId());
            $entity->load(1);
            $entity->setData($code, $snapshotValue);
            $entity->save();
        }

        $entity = Bootstrap::getObjectManager()->create(Product::class);
        $entity->setStoreId($store->getId());
        $entity->load(1);

        $updateHandler = Bootstrap::getObjectManager()->create(UpdateHandler::class);
        $entityData = array_merge($entity->getData(), [$code => $newValue]);
        $updateHandler->execute(ProductInterface::class, $entityData);

        $resultEntity = Bootstrap::getObjectManager()->create(Product::class);
        $resultEntity->setStoreId($store->getId());
        $resultEntity->load(1);

        $this->assertSame($expected, $resultEntity->getData($code));
    }

    /**
     * @return array
     */
    public function getCustomStoreDataProvider()
    {
        return [
            ['description', '', 'not_empty_value', 'not_empty_value'],                  //0
            ['description', '', '', null],                                              //1
            ['description', '', null, 'Description with <b>html tag</b>'],              //2
            ['description', '', false, 'Description with <b>html tag</b>'],             //3

            ['description', 'not_empty_value', 'not_empty_value2', 'not_empty_value2'], //4
            ['description', 'not_empty_value', '', null],                               //5
            ['description', 'not_empty_value', null, 'Description with <b>html tag</b>'], //6
            ['description', 'not_empty_value', false, 'Description with <b>html tag</b>'], //7

            ['description', null, 'not_empty_value', 'not_empty_value'],                 //8
            ['description', null, '', null],                                             //9
            ['description', null, false, 'Description with <b>html tag</b>'],            //10

            ['description', false, 'not_empty_value', 'not_empty_value'],                //11
            ['description', false, '', null],                                            //12
            ['description', false, null, 'Description with <b>html tag</b>'],            //13
        ];
    }

}
