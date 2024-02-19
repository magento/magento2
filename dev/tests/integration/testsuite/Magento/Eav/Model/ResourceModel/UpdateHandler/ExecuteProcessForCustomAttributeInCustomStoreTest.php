<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\ResourceModel\UpdateHandler;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\ResourceModel\UpdateHandlerAbstract;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Eav\Model\ResourceModel\UpdateHandler;

/**
 * @magentoAppArea adminhtml
 * @magentoAppIsolation enabled
 */
class ExecuteProcessForCustomAttributeInCustomStoreTest extends UpdateHandlerAbstract
{
    /**
     * @covers \Magento\Eav\Model\ResourceModel\UpdateHandler::execute
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Catalog/_files/dropdown_attribute.php
     * @magentoDataFixture Magento/Store/_files/second_store.php
     * @dataProvider getCustomAttributeDataProvider
     * @param $code
     * @param $defaultStoreValue
     * @param $snapshotValue
     * @param $newValue
     * @param $expected
     * @magentoDbIsolation disabled
     */
    public function testExecuteProcessForCustomAttributeInCustomStore(
        $code,
        $defaultStoreValue,
        $snapshotValue,
        $newValue,
        $expected
    ) {
        $store = Bootstrap::getObjectManager()->create(Store::class);
        $store->load('fixture_second_store', 'code');

        $this->reindexAll();

        $attribute = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            Attribute::class
        );
        $attribute->loadByCode(4, $code);

        $options = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection::class
        );
        $options->setAttributeFilter($attribute->getId());
        $optionIds = $options->getAllIds();

        $entity = Bootstrap::getObjectManager()->create(Product::class);
        $entity->setStoreId(0);
        $entity->load(1);
        $entity->setData($code, $optionIds[$defaultStoreValue]);
        $entity->save();

        if ($snapshotValue !== '-') {
            /** @var $options \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection */
            $entity = Bootstrap::getObjectManager()->create(Product::class);
            $entity->setStoreId($store->getId());
            $entity->load(1);

            if ($snapshotValue) {
                $snapshotValue = $optionIds[$snapshotValue];
            }

            $entity->setData($code, $snapshotValue);
            $entity->save();
        }

        $entity = Bootstrap::getObjectManager()->create(Product::class);
        $entity->setStoreId($store->getId());
        $entity->load(1);

        $updateHandler = Bootstrap::getObjectManager()->create(UpdateHandler::class);

        if ($newValue) {
            $newValue = $optionIds[$newValue];
        }

        $entityData = array_merge($entity->getData(), [$code => $newValue]);
        $updateHandler->execute(\Magento\Catalog\Api\Data\ProductInterface::class, $entityData);

        $resultEntity = Bootstrap::getObjectManager()->create(Product::class);
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
