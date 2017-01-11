<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\ResourceModel;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 * @magentoAppIsolation enabled
 */
class UpdateHandlerTest extends \Magento\TestFramework\Indexer\TestCase
{
    /**
     * @covers       \Magento\Eav\Model\ResourceModel\UpdateHandler::execute
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @dataProvider getAllStoresDataProvider
     * @param $code
     * @param $snapshotValue
     * @param $newValue
     * @param $expected
     */
    public function testExecuteProcessForAllStores($code, $snapshotValue, $newValue, $expected)
    {
        if ($snapshotValue !== '-') {
            $entity = Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
            $entity->setStoreId(0);
            $entity->load(1);
            $entity->setData($code, $snapshotValue);
            $entity->save();
        }

        $entity = Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
        $entity->setStoreId(0);
        $entity->load(1);

        $updateHandler = Bootstrap::getObjectManager()->create(UpdateHandler::class);
        $entityData = array_merge($entity->getData(), [$code => $newValue]);
        $updateHandler->execute(\Magento\Catalog\Api\Data\ProductInterface::class, $entityData);

        $resultEntity = Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
        $resultEntity->setStoreId(0);
        $resultEntity->load(1);

        $this->assertSame($expected, $resultEntity->getData($code));
    }

    /**
     * @covers       \Magento\Eav\Model\ResourceModel\UpdateHandlerTest::execute
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @dataProvider getCustomStoreDataProvider
     * @param $code
     * @param $snapshotValue
     * @param $newValue
     * @param $expected
     */
    public function testExecuteProcessForCustomStore($code, $snapshotValue, $newValue, $expected)
    {
        $store = Bootstrap::getObjectManager()->create(\Magento\Store\Model\Store::class);
        $store->load('fixture_second_store', 'code');

        Bootstrap::getObjectManager()
            ->create(\Magento\CatalogSearch\Model\Indexer\Fulltext\Processor::class)
            ->reindexAll();

        if ($snapshotValue !== '-') {
            $entity = Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
            $entity->setStoreId($store->getId());
            $entity->load(1);
            $entity->setData($code, $snapshotValue);
            $entity->save();
        }

        $entity = Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
        $entity->setStoreId($store->getId());
        $entity->load(1);

        $updateHandler = Bootstrap::getObjectManager()->create(UpdateHandler::class);
        $entityData = array_merge($entity->getData(), [$code => $newValue]);
        $updateHandler->execute(\Magento\Catalog\Api\Data\ProductInterface::class, $entityData);

        $resultEntity = Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
        $resultEntity->setStoreId($store->getId());
        $resultEntity->load(1);

        $this->assertSame($expected, $resultEntity->getData($code));
    }

    /**
     * @covers       \Magento\Eav\Model\ResourceModel\UpdateHandlerTest::execute
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Catalog/_files/dropdown_attribute.php
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @dataProvider getCustomAttributeDataProvider
     * @param $code
     * @param $defaultStoreValue
     * @param $snapshotValue
     * @param $newValue
     * @param $expected
     */
    public function testExecuteProcessForCustomAttributeInCustomStore(
        $code,
        $defaultStoreValue,
        $snapshotValue,
        $newValue,
        $expected
    ) {
        $store = Bootstrap::getObjectManager()->create(\Magento\Store\Model\Store::class);
        $store->load('fixture_second_store', 'code');

        Bootstrap::getObjectManager()
            ->create(\Magento\CatalogSearch\Model\Indexer\Fulltext\Processor::class)
            ->reindexAll();

        $attribute = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class
        );
        $attribute->loadByCode(4, $code);

        $options = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection::class
        );
        $options->setAttributeFilter($attribute->getId());
        $optionIds = $options->getAllIds();

        $entity = Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
        $entity->setStoreId(0);
        $entity->load(1);
        $entity->setData($code, $optionIds[$defaultStoreValue]);
        $entity->save();

        if ($snapshotValue !== '-') {
            /** @var $options \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection */
            $entity = Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
            $entity->setStoreId($store->getId());
            $entity->load(1);

            if ($snapshotValue) {
                $snapshotValue = $optionIds[$snapshotValue];
            }

            $entity->setData($code, $snapshotValue);
            $entity->save();
        }

        $entity = Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
        $entity->setStoreId($store->getId());
        $entity->load(1);

        $updateHandler = Bootstrap::getObjectManager()->create(UpdateHandler::class);

        if ($newValue) {
            $newValue = $optionIds[$newValue];
        }

        $entityData = array_merge($entity->getData(), [$code => $newValue]);
        $updateHandler->execute(\Magento\Catalog\Api\Data\ProductInterface::class, $entityData);

        $resultEntity = Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
        $resultEntity->setStoreId($store->getId());
        $resultEntity->load(1);

        if ($expected !== null) {
            $expected = $optionIds[$expected];
        }

        $this->assertSame($expected, $resultEntity->getData($code));
    }

    /**
     * @return array
     */
    public function getAllStoresDataProvider()
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

    /**
     * @return array
     */
    public function getCustomAttributeDataProvider()
    {
        return [
            ['dropdown_attribute', 0, '', 1, 1],        //0
            ['dropdown_attribute', 0, '', '', null],    //1
            ['dropdown_attribute', 0, '', null, 0],     //2
            ['dropdown_attribute', 0, '', false, 0],    //3

            ['dropdown_attribute', 0, 1, 2, 2],         //4
            ['dropdown_attribute', 0, 1, '', null],     //5
            ['dropdown_attribute', 0, 1, null, 0],      //6
            ['dropdown_attribute', 0, 1, false, 0],     //7

            ['dropdown_attribute', 0, null, 1, 1],      //8
            ['dropdown_attribute', 0, null, '', null],  //9
            ['dropdown_attribute', 0, null, false, 0],  //10

            ['dropdown_attribute', 0, false, 1, 1],     //11
            ['dropdown_attribute', 0, false, '', null], //12
            ['dropdown_attribute', 0, false, null, 0],  //13

            ['dropdown_attribute', 0, '-', 1, 1],       //14
            ['dropdown_attribute', 0, '-', '', null],   //15
            ['dropdown_attribute', 0, '-', null, 0],    //16
            ['dropdown_attribute', 0, '-', false, 0],   //17
        ];
    }
}
