<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model;

class ExportTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Model object which used for tests
     *
     * @var \Magento\ImportExport\Model\Export
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\ImportExport\Model\Export::class
        );
    }

    /**
     * Test method '_getEntityAdapter' in case when entity is valid
     *
     * @param string $entity
     * @param string $expectedEntityType
     * @dataProvider getEntityDataProvider
     * @covers \Magento\ImportExport\Model\Export::_getEntityAdapter
     */
    public function testGetEntityAdapterWithValidEntity($entity, $expectedEntityType)
    {
        $this->_model->setData(['entity' => $entity]);
        $this->_model->getEntityAttributeCollection();
        $this->assertAttributeInstanceOf(
            $expectedEntityType,
            '_entityAdapter',
            $this->_model,
            'Entity adapter property has wrong type'
        );
    }

    /**
     * @return array
     */
    public function getEntityDataProvider()
    {
        return [
            'product' => [
                '$entity' => 'catalog_product',
                '$expectedEntityType' => \Magento\CatalogImportExport\Model\Export\Product::class,
            ],
            'customer main data' => [
                '$entity' => 'customer',
                '$expectedEntityType' => \Magento\CustomerImportExport\Model\Export\Customer::class,
            ],
            'customer address' => [
                '$entity' => 'customer_address',
                '$expectedEntityType' => \Magento\CustomerImportExport\Model\Export\Address::class,
            ]
        ];
    }

    /**
     * Test method '_getEntityAdapter' in case when entity is invalid
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @covers \Magento\ImportExport\Model\Export::_getEntityAdapter
     */
    public function testGetEntityAdapterWithInvalidEntity()
    {
        $this->_model->setData(['entity' => 'test']);
        $this->_model->getEntityAttributeCollection();
    }
}
