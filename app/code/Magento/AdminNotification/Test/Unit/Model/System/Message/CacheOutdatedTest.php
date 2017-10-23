<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Test\Unit\Model\System\Message;

class CacheOutdatedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_authorizationMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheTypeListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlInterfaceMock;

    /**
     * @var \Magento\AdminNotification\Model\System\Message\CacheOutdated
     */
    protected $_messageModel;

    protected function setUp()
    {
        $this->_authorizationMock = $this->createMock(\Magento\Framework\AuthorizationInterface::class);
        $this->_urlInterfaceMock = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->_cacheTypeListMock = $this->createMock(\Magento\Framework\App\Cache\TypeListInterface::class);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $arguments = [
            'authorization' => $this->_authorizationMock,
            'urlBuilder' => $this->_urlInterfaceMock,
            'cacheTypeList' => $this->_cacheTypeListMock,
        ];
        $this->_messageModel = $objectManagerHelper->getObject(
            \Magento\AdminNotification\Model\System\Message\CacheOutdated::class,
            $arguments
        );
    }

    /**
     * @param string $expectedSum
     * @param array $cacheTypes
     * @dataProvider getIdentityDataProvider
     */
    public function testGetIdentity($expectedSum, $cacheTypes)
    {
        $this->_cacheTypeListMock->expects(
            $this->any()
        )->method(
            'getInvalidated'
        )->will(
            $this->returnValue($cacheTypes)
        );
        $this->assertEquals($expectedSum, $this->_messageModel->getIdentity());
    }

    public function getIdentityDataProvider()
    {
        $cacheTypeMock1 = $this->createPartialMock(\stdClass::class, ['getCacheType']);
        $cacheTypeMock1->expects($this->any())->method('getCacheType')->will($this->returnValue('Simple'));

        $cacheTypeMock2 = $this->createPartialMock(\stdClass::class, ['getCacheType']);
        $cacheTypeMock2->expects($this->any())->method('getCacheType')->will($this->returnValue('Advanced'));

        return [
            ['c13cfaddc2c53e8d32f59bfe89719beb', [$cacheTypeMock1]],
            ['69aacdf14d1d5fcef7168b9ac308215e', [$cacheTypeMock1, $cacheTypeMock2]]
        ];
    }

    /**
     * @param bool $expected
     * @param bool $allowed
     * @param array $cacheTypes
     * @dataProvider isDisplayedDataProvider
     */
    public function testIsDisplayed($expected, $allowed, $cacheTypes)
    {
        $this->_authorizationMock->expects($this->once())->method('isAllowed')->will($this->returnValue($allowed));
        $this->_cacheTypeListMock->expects(
            $this->any()
        )->method(
            'getInvalidated'
        )->will(
            $this->returnValue($cacheTypes)
        );
        $this->assertEquals($expected, $this->_messageModel->isDisplayed());
    }

    public function isDisplayedDataProvider()
    {
        $cacheTypesMock = $this->createPartialMock(\stdClass::class, ['getCacheType']);
        $cacheTypesMock->expects($this->any())->method('getCacheType')->will($this->returnValue('someVal'));
        $cacheTypes = [$cacheTypesMock, $cacheTypesMock];
        return [
            [false, false, []],
            [false, false, $cacheTypes],
            [false, true, []],
            [true, true, $cacheTypes]
        ];
    }

    public function testGetText()
    {
        $messageText = 'One or more of the Cache Types are invalidated';

        $this->_cacheTypeListMock->expects($this->any())->method('getInvalidated')->will($this->returnValue([]));
        $this->_urlInterfaceMock->expects($this->once())->method('getUrl')->will($this->returnValue('someURL'));
        $this->assertContains($messageText, $this->_messageModel->getText());
    }

    public function testGetLink()
    {
        $url = 'backend/admin/cache';
        $this->_urlInterfaceMock->expects($this->once())->method('getUrl')->will($this->returnValue($url));
        $this->assertEquals($url, $this->_messageModel->getLink());
    }
}
