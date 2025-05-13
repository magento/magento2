<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminNotification\Test\Unit\Model\System\Message;

use Magento\AdminNotification\Model\System\Message\CacheOutdated;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CacheOutdatedTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $_authorizationMock;

    /**
     * @var MockObject
     */
    protected $_cacheTypeListMock;

    /**
     * @var MockObject
     */
    protected $_urlInterfaceMock;

    /**
     * @var CacheOutdated
     */
    protected $_messageModel;

    protected function setUp(): void
    {
        $this->_authorizationMock = $this->getMockForAbstractClass(AuthorizationInterface::class);
        $this->_urlInterfaceMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->_cacheTypeListMock = $this->getMockForAbstractClass(TypeListInterface::class);

        $objectManagerHelper = new ObjectManager($this);
        $arguments = [
            'authorization' => $this->_authorizationMock,
            'urlBuilder' => $this->_urlInterfaceMock,
            'cacheTypeList' => $this->_cacheTypeListMock,
        ];
        $this->_messageModel = $objectManagerHelper->getObject(
            CacheOutdated::class,
            $arguments
        );
    }

    /**
     * @param string $expectedSum
     * @param array $cacheTypes
     * @dataProvider getIdentityDataProvider
     */
    public function testGetIdentity($expectedSum, $types)
    {
        $cacheType = [];
        foreach ($types as $type) {
            $cacheType[] = $type($this);
        }

        $this->_cacheTypeListMock->method(
            'getInvalidated'
        )->willReturn(
            $cacheType
        );
        $this->assertEquals($expectedSum, $this->_messageModel->getIdentity());
    }

    protected function getMockForStdClass($mockReturn)
    {
        $cacheTypeMock = $this->getMockBuilder(\stdClass::class)->addMethods(['getCacheType'])
            ->disableOriginalConstructor()
            ->getMock();
        $cacheTypeMock->method('getCacheType')->willReturn($mockReturn);
        return $cacheTypeMock;
    }

    /**
     * @return array
     */
    public static function getIdentityDataProvider()
    {
        $cacheTypeMock1 = static fn (self $testCase) => $testCase->getMockForStdClass('Simple');
        $cacheTypeMock2 = static fn (self $testCase) => $testCase->getMockForStdClass('Advanced');

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
        $cacheType1 = [];
        foreach ($cacheTypes as $cacheType) {
            $cacheType1[] = $cacheType($this);
        }
        $this->_authorizationMock->expects($this->once())->method('isAllowed')->willReturn($allowed);
        $this->_cacheTypeListMock->method(
            'getInvalidated'
        )->willReturn(
            $cacheType1
        );
        $this->assertEquals($expected, $this->_messageModel->isDisplayed());
    }

    /**
     * @return array
     */
    public static function isDisplayedDataProvider()
    {
        $cacheTypesMock = static fn (self $testCase) => $testCase->getMockForStdClass('someVal');
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

        $this->_cacheTypeListMock->method('getInvalidated')->willReturn([]);
        $this->_urlInterfaceMock->expects($this->once())->method('getUrl')->willReturn('someURL');
        $this->assertStringContainsString($messageText, $this->_messageModel->getText());
    }

    public function testGetLink()
    {
        $url = 'backend/admin/cache';
        $this->_urlInterfaceMock->expects($this->once())->method('getUrl')->willReturn($url);
        $this->assertEquals($url, $this->_messageModel->getLink());
    }
}
