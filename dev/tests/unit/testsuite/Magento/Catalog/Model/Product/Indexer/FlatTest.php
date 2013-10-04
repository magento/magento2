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
 * @category    Magento
 * @package     Magento_Catalog
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\Catalog\Model\Product\Indexer\Flat
 */
namespace Magento\Catalog\Model\Product\Indexer;

class FlatTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Indexer\Flat
     */
    protected $_model = null;

    /**
     * @var \Magento\Index\Model\Event
     */
    protected $_event = null;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $indexerFactoryMock = $this->getMock('Magento\Catalog\Model\Product\Flat\IndexerFactory', array(), array(),
            '', false);
        $this->_model = $objectManagerHelper->getObject('Magento\Catalog\Model\Product\Indexer\Flat', array(
            'flatIndexerFactory' => $indexerFactoryMock,
        ));
        $this->_event = $this->getMock('Magento\Index\Model\Event',
            array('getFlatHelper', 'getEntity', 'getType', 'getDataObject'), array(), '', false
        );
    }

    public function testMatchEventAvailability()
    {
        $flatHelper = $this->getMock('Magento\Catalog\Helper\Product\Flat', array(), array(), '', false, false);
        $flatHelper->expects($this->any())
            ->method('isAvailable')
            ->will($this->returnValue(false));

        $this->_event->expects($this->any())
            ->method('getFlatHelper')
            ->will($this->returnValue($flatHelper));

        $this->assertFalse($this->_model->matchEvent($this->_event));

        $flatHelper->expects($this->any())
            ->method('isBuilt')
            ->will($this->returnValue(false));

        $this->assertFalse($this->_model->matchEvent($this->_event));
    }

    /**
     * @dataProvider getEavAttributeProvider
     */
    public function testMatchEventForEavAttribute($attributeValue, $addFilterable, $origData, $data, $eventType,
        $result
    ) {
        $flatHelper = $this->getMock('Magento\Catalog\Helper\Product\Flat', array(), array(), '', false);
        $flatHelper->expects($this->any())
            ->method('isAvailable')
            ->will($this->returnValue(true));
        $flatHelper->expects($this->any())
            ->method('isBuilt')
            ->will($this->returnValue(true));

        $this->_event->expects($this->any())
            ->method('getFlatHelper')
            ->will($this->returnValue($flatHelper));

        $this->_event->expects($this->any())
            ->method('getEntity')
            ->will($this->returnValue(\Magento\Catalog\Model\Resource\Eav\Attribute::ENTITY));

        if ($attributeValue) {
            $attributeValue = $this->getMockBuilder('Magento\Catalog\Model\Resource\Eav\Attribute')
                ->disableOriginalConstructor()
                ->setMethods(array('getData', 'getOrigData'))
                ->getMock();
        }
        $this->_event->expects($this->any())
            ->method('getDataObject')
            ->will($this->returnValue($attributeValue));

        $flatHelper->expects($this->any())
            ->method('isAddFilterableAttributes')
            ->will($this->returnValue($addFilterable));

        if (!$attributeValue) {
            $this->assertEquals($result, $this->_model->matchEvent($this->_event));
            return;
        }

        $attributeValue->expects($this->any())
            ->method('getData')
            ->will($this->returnValueMap($data));

        $attributeValue->expects($this->any())
            ->method('getOrigData')
            ->will($this->returnValueMap($origData));

        $this->_event->expects($this->any())
            ->method('getType')
            ->will($this->returnValue($eventType));
        $this->assertEquals($result, $this->_model->matchEvent($this->_event));
    }

    public function testMatchEventForStoreForDelete()
    {
        $this->_prepareStoreConfiguration();

        $this->_event->expects($this->any())
            ->method('getType')
            ->will($this->returnValue(\Magento\Index\Model\Event::TYPE_DELETE));

        $this->assertTrue($this->_model->matchEvent($this->_event));
    }

    public function testMatchEventForEmptyStoreForSave()
    {
        $this->_prepareStoreConfiguration();

        $this->_event->expects($this->any())
            ->method('getType')
            ->will($this->returnValue(\Magento\Index\Model\Event::TYPE_SAVE));

        $this->_event->expects($this->any())
            ->method('getDataObject')
            ->will($this->returnValue(null));

        $this->assertFalse($this->_model->matchEvent($this->_event));
    }

    public function testMatchEventForOldStoreForSave()
    {
        $this->_prepareStoreConfiguration();

        $this->_event->expects($this->any())
            ->method('getType')
            ->will($this->returnValue(\Magento\Index\Model\Event::TYPE_SAVE));

        $store = $this->getMockBuilder('Magento\Core\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();

        $store->expects($this->any())
            ->method('isObjectNew')
            ->will($this->returnValue(false));

        $this->_event->expects($this->any())
            ->method('getDataObject')
            ->will($this->returnValue($store));

        $this->assertFalse($this->_model->matchEvent($this->_event));
    }

    public function testMatchEventForNewStoreForSave()
    {
        $this->_prepareStoreConfiguration();

        $this->_event->expects($this->any())
            ->method('getType')
            ->will($this->returnValue(\Magento\Index\Model\Event::TYPE_SAVE));

        $store = $this->getMockBuilder('Magento\Core\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();

        $store->expects($this->any())
            ->method('isObjectNew')
            ->will($this->returnValue(true));

        $this->_event->expects($this->any())
            ->method('getDataObject')
            ->will($this->returnValue($store));

        $this->assertTrue($this->_model->matchEvent($this->_event));
    }

    protected function _prepareStoreConfiguration()
    {
        $flatHelper = $this->getMock('Magento\Catalog\Helper\Product\Flat', array(), array(), '', false);
        $flatHelper->expects($this->any())
            ->method('isAvailable')
            ->will($this->returnValue(true));
        $flatHelper->expects($this->any())
            ->method('isBuilt')
            ->will($this->returnValue(true));

        $this->_event->expects($this->any())
            ->method('getFlatHelper')
            ->will($this->returnValue($flatHelper));

        $this->_event->expects($this->any())
            ->method('getEntity')
            ->will($this->returnValue(\Magento\Core\Model\Store::ENTITY));
    }

    public function testMatchEventForEmptyStoreGroup()
    {
        $flatHelper = $this->getMock('Magento\Catalog\Helper\Product\Flat', array(), array(), '', false);
        $flatHelper->expects($this->any())
            ->method('isAvailable')
            ->will($this->returnValue(true));
        $flatHelper->expects($this->any())
            ->method('isBuilt')
            ->will($this->returnValue(true));

        $this->_event->expects($this->any())
            ->method('getFlatHelper')
            ->will($this->returnValue($flatHelper));

        $this->_event->expects($this->any())
            ->method('getEntity')
            ->will($this->returnValue(\Magento\Core\Model\Store\Group::ENTITY));

        $this->_event->expects($this->any())
            ->method('getDataObject')
            ->will($this->returnValue(null));

        $this->assertFalse($this->_model->matchEvent($this->_event));
    }


    public function testMatchEventForNotChangedStoreGroup()
    {
        $flatHelper = $this->getMock('Magento\Catalog\Helper\Product\Flat', array(), array(), '', false);
        $flatHelper->expects($this->any())
            ->method('isAvailable')
            ->will($this->returnValue(true));
        $flatHelper->expects($this->any())
            ->method('isBuilt')
            ->will($this->returnValue(true));

        $this->_event->expects($this->any())
            ->method('getFlatHelper')
            ->will($this->returnValue($flatHelper));

        $this->_event->expects($this->any())
            ->method('getEntity')
            ->will($this->returnValue(\Magento\Core\Model\Store\Group::ENTITY));

        $storeGroup = $this->getMockBuilder('Magento\Core\Model\Store\Group')
            ->disableOriginalConstructor()
            ->getMock();

        $storeGroup->expects($this->any())
            ->method('dataHasChangedFor')
            ->will($this->returnValue(false));

        $this->_event->expects($this->any())
            ->method('getDataObject')
            ->will($this->returnValue($storeGroup));

        $this->assertFalse($this->_model->matchEvent($this->_event));
    }

    public function testMatchEventForChangedStoreGroup()
    {
        $flatHelper = $this->getMock('Magento\Catalog\Helper\Product\Flat', array(), array(), '', false);
        $flatHelper->expects($this->any())
            ->method('isAvailable')
            ->will($this->returnValue(true));
        $flatHelper->expects($this->any())
            ->method('isBuilt')
            ->will($this->returnValue(true));

        $this->_event->expects($this->any())
            ->method('getFlatHelper')
            ->will($this->returnValue($flatHelper));

        $this->_event->expects($this->any())
            ->method('getEntity')
            ->will($this->returnValue(\Magento\Core\Model\Store\Group::ENTITY));

        $storeGroup = $this->getMockBuilder('Magento\Core\Model\Store\Group')
            ->disableOriginalConstructor()
            ->getMock();

        $storeGroup->expects($this->any())
            ->method('dataHasChangedFor')
            ->will($this->returnValue(true));

        $this->_event->expects($this->any())
            ->method('getDataObject')
            ->will($this->returnValue($storeGroup));

        $this->assertTrue($this->_model->matchEvent($this->_event));
    }

    public function testMatchEventParentFallback()
    {
        $flatHelper = $this->getMock('Magento\Catalog\Helper\Product\Flat', array(), array(), '', false);
        $flatHelper->expects($this->any())
            ->method('isAvailable')
            ->will($this->returnValue(true));
        $flatHelper->expects($this->any())
            ->method('isBuilt')
            ->will($this->returnValue(true));

        $this->_event->expects($this->any())
            ->method('getFlatHelper')
            ->will($this->returnValue($flatHelper));

        $this->_event->expects($this->any())
            ->method('getEntity')
            ->will($this->returnValue('some_value'));

        $this->assertFalse($this->_model->matchEvent($this->_event));
    }

    public function testMatchEventCaching()
    {
        $this->testMatchEventForChangedStoreGroup();

        $storeGroup = $this->_event->getDataObject();

        $storeGroup->expects($this->any())
            ->method('dataHasChangedFor')
            ->will($this->returnValue(false));

        $this->assertTrue($this->_model->matchEvent($this->_event));

    }

    /**
     * Provider for testMatchEventForEavAttribute
     */
    public static function getEavAttributeProvider()
    {
        return include __DIR__ . '/../../../_files/eav_attributes_data.php';
    }

}
