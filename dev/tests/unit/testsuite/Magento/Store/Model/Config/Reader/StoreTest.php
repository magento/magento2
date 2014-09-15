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
namespace Magento\Store\Model\Config\Reader;

class StoreTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Model\Config\Reader\Store
     */
    protected $_model;

    /**
     * @var \Magento\Framework\App\Config\ScopePool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopePullMock;

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
        $this->_scopePullMock = $this->getMock('Magento\Framework\App\Config\ScopePool', [], [], '', false);
        $this->_storeManagerMock = $this->getMock('Magento\Framework\StoreManagerInterface');
        $this->_initialConfigMock = $this->getMock('Magento\Framework\App\Config\Initial', [], [], '', false);
        $this->_collectionFactory = $this->getMock(
            'Magento\Store\Model\Resource\Config\Collection\ScopedFactory',
            ['create'],
            [],
            '',
            false
        );
        $storeFactoryMock = $this->getMock('Magento\Store\Model\StoreFactory', ['create'], [], '', false);
        $this->_storeMock = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $storeFactoryMock->expects($this->any())->method('create')->will($this->returnValue($this->_storeMock));

        $this->_appStateMock = $this->getMock('Magento\Framework\App\State', [], [], '', false);
        $this->_appStateMock->expects($this->any())->method('isInstalled')->will($this->returnValue(true));

        $placeholderProcessor = $this->getMock(
            'Magento\Store\Model\Config\Processor\Placeholder',
            [],
            [],
            '',
            false
        );
        $placeholderProcessor->expects($this->any())->method('process')->will($this->returnArgument(0));
        $this->_model = new \Magento\Store\Model\Config\Reader\Store(
            $this->_initialConfigMock,
            $this->_scopePullMock,
            new \Magento\Store\Model\Config\Converter($placeholderProcessor),
            $this->_collectionFactory,
            $storeFactoryMock,
            $this->_appStateMock,
            $this->_storeManagerMock
        );
    }

    /**
     * @dataProvider readDataProvider
     * @param string|null $storeCode
     * @param string $storeMethod
     */
    public function testRead($storeCode, $storeMethod)
    {
        $websiteCode = 'default';
        $storeId = 1;
        $websiteMock = $this->getMock('Magento\Store\Model\Website', [], [], '', false);
        $websiteMock->expects($this->any())->method('getCode')->will($this->returnValue($websiteCode));
        $this->_storeMock->expects($this->any())->method('getWebsite')->will($this->returnValue($websiteMock));
        $this->_storeMock->expects($this->any())->method('load')->with($storeCode);
        $this->_storeMock->expects($this->any())->method('getId')->will($this->returnValue($storeId));
        $this->_storeMock->expects($this->any())->method('getCode')->will($this->returnValue($websiteCode));

        $dataMock = $this->getMock('Magento\Framework\App\Config\Data', [], [], '', false);
        $dataMock->expects(
            $this->any()
        )->method(
            'getValue'
        )->will(
            $this->returnValue(['config' => ['key0' => 'website_value0', 'key1' => 'website_value1']])
        );

        $dataMock->expects(
            $this->once()
        )->method(
            'getSource'
        )->will(
            $this->returnValue(['config' => ['key0' => 'website_value0', 'key1' => 'website_value1']])
        );
        $this->_scopePullMock->expects(
            $this->once()
        )->method(
            'getScope'
        )->with(
            'website',
            $websiteCode
        )->will(
            $this->returnValue($dataMock)
        );

        $this->_initialConfigMock->expects(
            $this->once()
        )->method(
            'getData'
        )->with(
            "stores|{$storeCode}"
        )->will(
            $this->returnValue(['config' => ['key1' => 'store_value1', 'key2' => 'store_value2']])
        );
        $this->_collectionFactory->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            ['scope' => 'stores', 'scopeId' => $storeId]
        )->will(
            $this->returnValue(
                [
                    new \Magento\Framework\Object(['path' => 'config/key1', 'value' => 'store_db_value1']),
                    new \Magento\Framework\Object(['path' => 'config/key3', 'value' => 'store_db_value3'])
                ]
            )
        );

        $this->_storeManagerMock
            ->expects($this->any())
            ->method($storeMethod)
            ->will($this->returnValue($this->_storeMock));
        $expectedData = array(
            'config' => array(
                'key0' => 'website_value0',
                'key1' => 'store_db_value1',
                'key2' => 'store_value2',
                'key3' => 'store_db_value3'
            )
        );
        $this->assertEquals($expectedData, $this->_model->read($storeCode));
    }

    public function readDataProvider()
    {
        return array(
            array('default', 'getDefaultStoreView'),
            array(null, 'getStore'),
            array('code', '')
        );
    }
}
