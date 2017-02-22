<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model;

class ExportTest extends \PHPUnit_Framework_TestCase
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
            'Magento\ImportExport\Model\Export'
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
                '$expectedEntityType' => 'Magento\CatalogImportExport\Model\Export\Product',
            ],
            'customer main data' => [
                '$entity' => 'customer',
                '$expectedEntityType' => 'Magento\CustomerImportExport\Model\Export\Customer',
            ],
            'customer address' => [
                '$entity' => 'customer_address',
                '$expectedEntityType' => 'Magento\CustomerImportExport\Model\Export\Address',
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
