<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Unit\Model;

use Magento\Security\Model\SecurityCookie;

/**
 * Test class for \Magento\Security\Model\SecurityCookie testing
 */
class SecurityCookieTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager */
    protected $phpCookieManagerMock;

    /** @var \Magento\Framework\Stdlib\Cookie\PublicCookieMetadataFactory */
    protected $cookieMetadataFactoryMock;

    /** @var \Magento\Framework\Stdlib\Cookie\PublicCookieMetadata */
    protected $cookieMetadataMock;

    /** @var \Magento\Framework\Stdlib\Cookie\CookieReaderInterface */
    protected $cookieReaderMock;

    /** @var \Magento\Framework\Stdlib\Cookie\PublicCookieMetadata */
    protected $backendDataMock;

    /** @var SecurityCookie */
    protected $model;

    /**
     * Init mocks for tests
     * @return void
     */
    protected function setUp()
    {
        $this->phpCookieManagerMock = $this->createPartialMock(
            \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class,
            ['setPublicCookie']
        );

        $this->cookieMetadataFactoryMock = $this->createPartialMock(
            \Magento\Framework\Stdlib\Cookie\PublicCookieMetadataFactory::class,
            ['create']
        );

        $this->cookieMetadataMock = $this->createPartialMock(
            \Magento\Framework\Stdlib\Cookie\PublicCookieMetadata::class,
            ['setPath', 'setDuration']
        );

        $this->cookieReaderMock = $this->createPartialMock(
            \Magento\Framework\Stdlib\Cookie\CookieReaderInterface::class,
            ['getCookie']
        );

        $this->backendDataMock = $this->createMock(\Magento\Backend\Helper\Data::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            SecurityCookie::class,
            [
                'phpCookieManager' => $this->phpCookieManagerMock,
                'cookieMetadataFactory' => $this->cookieMetadataFactoryMock,
                'cookieReader' => $this->cookieReaderMock,
                'backendData' => $this->backendDataMock
            ]
        );
    }

    /**
     * Test get logout reason cookie
     * @return void
     */
    public function testGetLogoutReasonCookie()
    {
        $cookie = '123';

        $this->cookieReaderMock->expects($this->once())
            ->method('getCookie')
            ->with(
                SecurityCookie::LOGOUT_REASON_CODE_COOKIE_NAME,
                -1
            )
            ->willReturn($cookie);

        $this->assertEquals((int)$cookie, $this->model->getLogoutReasonCookie());
    }

    /**
     * Test set logout reason cookie
     * @return void
     */
    public function testSetLogoutReasonCookie()
    {
        $status = '3';
        $frontName = 'FrontName';

        $this->createCookieMetaData();

        $this->backendDataMock->expects($this->once())
            ->method('getAreaFrontName')
            ->willReturn($frontName);

        $this->cookieMetadataMock->expects($this->once())
            ->method('setPath')
            ->with('/' . $frontName)
            ->willReturnSelf();

        $this->phpCookieManagerMock->expects($this->once())
            ->method('setPublicCookie')
            ->with(
                SecurityCookie::LOGOUT_REASON_CODE_COOKIE_NAME,
                (int)$status,
                $this->cookieMetadataMock
            )
            ->willReturnSelf();

        $this->assertEquals($this->model, $this->model->setLogoutReasonCookie($status));
    }

    /**
     * Test delete logout reason cookie
     * @return void
     */
    public function testDeleteLogoutReasonCookie()
    {
        $frontName = 'FrontName';

        $this->createCookieMetaData();

        $this->backendDataMock->expects($this->once())
            ->method('getAreaFrontName')
            ->willReturn($frontName);

        $this->cookieMetadataMock->expects($this->once())
            ->method('setPath')
            ->with('/' . $frontName)
            ->willReturnSelf();

        $this->cookieMetadataMock->expects($this->once())
            ->method('setDuration')
            ->with(-1)
            ->willReturnSelf();

        $this->phpCookieManagerMock->expects($this->once())
            ->method('setPublicCookie')
            ->with(
                SecurityCookie::LOGOUT_REASON_CODE_COOKIE_NAME,
                '',
                $this->cookieMetadataMock
            )
            ->willReturnSelf();

        $this->assertEquals($this->model, $this->model->deleteLogoutReasonCookie());
    }

    /**
     * @return void
     */
    protected function createCookieMetaData()
    {
        $this->cookieMetadataFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->cookieMetadataMock);
    }
}
