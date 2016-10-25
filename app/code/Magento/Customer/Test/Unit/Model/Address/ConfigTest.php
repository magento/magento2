<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Address;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_readerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_addressHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfigMock;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    protected $_model;

    /**
     * @var string
     */
    protected $_cacheId = 'cache_id';

    protected function setUp()
    {
        $this->_storeMock = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);
        $this->_scopeConfigMock = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $this->_readerMock = $this->getMock(
            \Magento\Customer\Model\Address\Config\Reader::class,
            [],
            [],
            '',
            false
        );
        $this->_cacheMock = $this->getMock(\Magento\Framework\Config\CacheInterface::class);
        $this->_storeManagerMock = $this->getMock(\Magento\Store\Model\StoreManager::class, [], [], '', false);
        $this->_storeManagerMock->expects(
            $this->once()
        )->method(
            'getStore'
        )->will(
            $this->returnValue($this->_storeMock)
        );

        $this->_addressHelperMock = $this->getMock(\Magento\Customer\Helper\Address::class, [], [], '', false);

        $this->_cacheMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            $this->_cacheId
        )->will(
            $this->returnValue(false)
        );

        $fixtureConfigData = require __DIR__ . '/Config/_files/formats_merged.php';

        $this->_readerMock->expects($this->once())->method('read')->will($this->returnValue($fixtureConfigData));

        $this->_cacheMock->expects($this->once())
            ->method('save')
            ->with(
                json_encode($fixtureConfigData),
                $this->_cacheId
            );

        $this->serializerMock = $this->getMock(\Magento\Framework\Serialize\SerializerInterface::class);
        $this->serializerMock->method('serialize')
            ->willReturn(json_encode($fixtureConfigData));
        $this->serializerMock->method('unserialize')
            ->willReturn($fixtureConfigData);

        $this->_model = new \Magento\Customer\Model\Address\Config(
            $this->_readerMock,
            $this->_cacheMock,
            $this->_storeManagerMock,
            $this->_addressHelperMock,
            $this->_scopeConfigMock,
            $this->_cacheId,
            $this->serializerMock
        );
    }

    public function testGetStore()
    {
        $this->assertEquals($this->_storeMock, $this->_model->getStore());
    }

    public function testSetStore()
    {
        $this->_model->setStore($this->_storeMock);

        //no call to $_storeManagerMock's method
        $this->assertEquals($this->_storeMock, $this->_model->getStore());
    }

    public function testGetFormats()
    {
        $this->_storeMock->expects($this->once())->method('getId');

        $this->_scopeConfigMock->expects($this->any())->method('getValue')->will($this->returnValue('someValue'));

        $rendererMock = $this->getMock(\Magento\Framework\DataObject::class);

        $this->_addressHelperMock->expects(
            $this->any()
        )->method(
            'getRenderer'
        )->will(
            $this->returnValue($rendererMock)
        );

        $firstExpected = new \Magento\Framework\DataObject();
        $firstExpected->setCode(
            'format_one'
        )->setTitle(
            'format_one_title'
        )->setDefaultFormat(
            'someValue'
        )->setEscapeHtml(
            false
        )->setRenderer(
            null
        );

        $secondExpected = new \Magento\Framework\DataObject();
        $secondExpected->setCode(
            'format_two'
        )->setTitle(
            'format_two_title'
        )->setDefaultFormat(
            'someValue'
        )->setEscapeHtml(
            true
        )->setRenderer(
            null
        );
        $expectedResult = [$firstExpected, $secondExpected];

        $this->assertEquals($expectedResult, $this->_model->getFormats());
    }
}
