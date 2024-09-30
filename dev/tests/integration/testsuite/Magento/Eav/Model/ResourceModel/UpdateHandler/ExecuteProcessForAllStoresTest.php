<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\ResourceModel\UpdateHandler;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\ResourceModel\UpdateHandler;
use Magento\Eav\Model\ResourceModel\UpdateHandlerAbstract;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 * @magentoAppIsolation enabled
 */
class ExecuteProcessForAllStoresTest extends UpdateHandlerAbstract
{
    /**
     * @covers \Magento\Eav\Model\ResourceModel\UpdateHandler::execute
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @dataProvider getAllStoresDataProvider
     * @param $code
     * @param $snapshotValue
     * @param $newValue
     * @param $expected
     * @magentoDbIsolation disabled
     */
    public function testExecuteProcessForAllStores($code, $snapshotValue, $newValue, $expected)
    {
        if ($snapshotValue !== '-') {
            $entity = Bootstrap::getObjectManager()->create(Product::class);
            $entity->setStoreId(0);
            $entity->load(1);
            $entity->setData($code, $snapshotValue);
            $entity->save();
        }

        $entity = Bootstrap::getObjectManager()->create(Product::class);
        $entity->setStoreId(0);
        $entity->load(1);

        $updateHandler = Bootstrap::getObjectManager()->create(UpdateHandler::class);
        $entityData = array_merge($entity->getData(), [$code => $newValue]);
        $updateHandler->execute(\Magento\Catalog\Api\Data\ProductInterface::class, $entityData);

        $resultEntity = Bootstrap::getObjectManager()->create(Product::class);
        $resultEntity->setStoreId(0);
        $resultEntity->load(1);

        $this->assertSame($expected, $resultEntity->getData($code));
    }

    /**
     * @return array
     */
    public static function getAllStoresDataProvider()
    {
        return [
            ['description', '', 'not_empty_value', 'not_empty_value'],                  //0
            ['description', '', '', null],                                              //1
            ['description', '', null, null],                                            //2
            ['description', '', false, null],                                           //3

            ['description', 'not_empty_value', 'not_empty_value2', 'not_empty_value2'], //4
            ['description', 'not_empty_value', '', null],                               //5
            ['description', 'not_empty_value', null, null],                             //6
            ['description', 'not_empty_value', false, null],                            //7

            ['description', null, 'not_empty_value', 'not_empty_value'],                //8
            ['description', null, '', null],                                            //9
            ['description', null, false, null],                                         //10

            ['description', false, 'not_empty_value', 'not_empty_value'],               //11
            ['description', false, '', null],                                           //12
            ['description', false, null, null],                                         //13
        ];
    }
}
