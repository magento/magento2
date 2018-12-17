<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Test\Unit\Model\System\Message;

use Magento\AdminNotification\Model\System\Message\CacheOutdated;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Class CacheOutdatedTest
 *
 * @package Magento\AdminNotification\Test\Unit\Model\System\Message
 */
class CacheOutdatedTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $authorizationMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheTypeListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlInterfaceMock;

    /**
     * @var CacheOutdated
     */
    protected $messageModel;

    protected function setUp()
    {
        $this->authorizationMock = $this->createMock(AuthorizationInterface::class);
        $this->urlInterfaceMock = $this->createMock(UrlInterface::class);
        $this->cacheTypeListMock = $this->createMock(TypeListInterface::class);

        $objectManagerHelper = new ObjectManager($this);
        $arguments = [
            'authorization' => $this->authorizationMock,
            'urlBuilder' => $this->urlInterfaceMock,
            'cacheTypeList' => $this->cacheTypeListMock,
        ];
        $this->messageModel = $objectManagerHelper->getObject(
            CacheOutdated::class,
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
        $this->cacheTypeListMock->expects(
            static::any()
        )->method(
            'getInvalidated'
        )->will(
            static::returnValue($cacheTypes)
        );
        static::assertEquals($expectedSum, $this->messageModel->getIdentity());
    }

    /**
     * @return array
     */
    public function getIdentityDataProvider()
    {
        $cacheTypeMock1 = $this->createPartialMock(stdClass::class, ['getCacheType']);
        $cacheTypeMock1->expects(static::any())->method('getCacheType')->will(static::returnValue('Simple'));

        $cacheTypeMock2 = $this->createPartialMock(stdClass::class, ['getCacheType']);
        $cacheTypeMock2->expects(static::any())->method('getCacheType')->will(static::returnValue('Advanced'));

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
        $this->authorizationMock->expects(static::once())->method('isAllowed')->will(static::returnValue($allowed));
        $this->cacheTypeListMock->expects(
            static::any()
        )->method(
            'getInvalidated'
        )->will(
            static::returnValue($cacheTypes)
        );
        static::assertEquals($expected, $this->messageModel->isDisplayed());
    }

    /**
     * @return array
     */
    public function isDisplayedDataProvider()
    {
        $cacheTypesMock = $this->createPartialMock(stdClass::class, ['getCacheType']);
        $cacheTypesMock->expects(static::any())->method('getCacheType')->will(static::returnValue('someVal'));
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

        $this->cacheTypeListMock->expects(static::any())->method('getInvalidated')->will(static::returnValue([]));
        $this->urlInterfaceMock->expects(static::once())->method('getUrl')->will(static::returnValue('someURL'));
        static::assertContains($messageText, $this->messageModel->getText());
    }

    public function testGetLink()
    {
        $url = 'backend/admin/cache';
        $this->urlInterfaceMock->expects(static::once())->method('getUrl')->will(static::returnValue($url));
        static::assertEquals($url, $this->messageModel->getLink());
    }
}
