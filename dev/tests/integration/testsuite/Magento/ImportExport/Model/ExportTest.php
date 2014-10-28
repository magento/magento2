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
        $this->_model->setData(array('entity' => $entity));
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
        return array(
            'product' => array(
                '$entity' => 'catalog_product',
                '$expectedEntityType' => 'Magento\CatalogImportExport\Model\Export\Product'
            ),
            'customer main data' => array(
                '$entity' => 'customer',
                '$expectedEntityType' => 'Magento\CustomerImportExport\Model\Export\Customer'
            ),
            'customer address' => array(
                '$entity' => 'customer_address',
                '$expectedEntityType' => 'Magento\CustomerImportExport\Model\Export\Address'
            )
        );
    }

    /**
     * Test method '_getEntityAdapter' in case when entity is invalid
     *
     * @expectedException \Magento\Framework\Model\Exception
     * @covers \Magento\ImportExport\Model\Export::_getEntityAdapter
     */
    public function testGetEntityAdapterWithInvalidEntity()
    {
        $this->_model->setData(array('entity' => 'test'));
        $this->_model->getEntityAttributeCollection();
    }
}
