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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\Config\Section\Reader;

class StoreTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Config\Section\Reader\Store
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_sectionPullMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_initialConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_collectionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_appStateMock;

    protected function setUp()
    {
        $this->_sectionPullMock = $this->getMock('Magento\Core\Model\Config\SectionPool', array(), array(), '', false);
        $this->_initialConfigMock = $this->getMock('Magento\Core\Model\Config\Initial', array(), array(), '', false);
        $this->_collectionFactory = $this->getMock(
            'Magento\Core\Model\Resource\Config\Value\Collection\ScopedFactory',
            array('create'),
            array(),
            '',
            false
        );
        $storeFactoryMock = $this->getMock('Magento\Core\Model\StoreFactory', array('create'), array(), '', false);
        $this->_storeMock = $this->getMock('Magento\Core\Model\Store', array(), array(), '', false);
        $storeFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->_storeMock));

        $this->_appStateMock = $this->getMock('Magento\App\State', array(), array(), '', false);
        $this->_appStateMock->expects($this->any())
            ->method('isInstalled')
            ->will($this->returnValue(true));

        $placeholderProcessor = $this->getMock(
            'Magento\Core\Model\Config\Section\Processor\Placeholder',
            array(),
            array(),
            '',
            false
        );
        $placeholderProcessor->expects($this->any())
            ->method('process')
            ->will($this->returnArgument(0));
        $this->_model = new \Magento\Core\Model\Config\Section\Reader\Store(
            $this->_initialConfigMock,
            $this->_sectionPullMock,
            new \Magento\Core\Model\Config\Section\Store\Converter($placeholderProcessor),
            $this->_collectionFactory,
            $storeFactoryMock,
            $this->_appStateMock
        );
    }

    public function testRead()
    {
        $websiteCode = 'default';
        $storeCode = 'default';
        $storeId = 1;
        $websiteMock = $this->getMock('Magento\Core\Model\Website', array(), array(), '', false);
        $websiteMock->expects($this->any())->method('getCode')->will($this->returnValue($websiteCode));
        $this->_storeMock->expects($this->any())->method('getWebsite')->will($this->returnValue($websiteMock));
        $this->_storeMock->expects($this->once())
            ->method('load')
            ->with($storeCode);
        $this->_storeMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($storeId));

        $sectionMock = $this->getMock('Magento\Core\Model\Config\Data', array(), array(), '', false);
        $sectionMock->expects($this->any())->method('getValue')->will($this->returnValue(array(
            'config' => array('key0' => 'website_value0', 'key1' => 'website_value1'),
        )));
        $sectionMock->expects($this->once())->method('getSource')->will($this->returnValue(array(
            'config' => array('key0' => 'website_value0', 'key1' => 'website_value1'),
        )));
        $this->_sectionPullMock->expects($this->once())
            ->method('getSection')
            ->with('website', $websiteCode)
            ->will($this->returnValue($sectionMock));

        $this->_initialConfigMock->expects($this->any())
            ->method('getStore')
            ->with($storeCode)
            ->will($this->returnValue(array(
                'config' => array('key1' => 'store_value1', 'key2' => 'store_value2'),
            )));
        $this->_collectionFactory->expects($this->once())
            ->method('create')
            ->with(array('scope' => 'stores', 'scopeId' => $storeId))
            ->will($this->returnValue(array(
                new \Magento\Object(array('path' => 'config/key1', 'value' => 'store_db_value1')),
                new \Magento\Object(array('path' => 'config/key3', 'value' => 'store_db_value3')),
            )));
        $expectedData = array(
            'config' => array(
                'key0' => 'website_value0', // value from website scope
                'key1' => 'store_db_value1',
                'key2' => 'store_value2', // value that has not been overridden in DB
                'key3' => 'store_db_value3'
            ),
        );
        $this->assertEquals($expectedData, $this->_model->read($storeCode));
    }
}
