<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Unit\Model;

use Magento\Security\Model\SecurityCookie;

/**
 * Test class for \Magento\Security\Model\SecurityCookie testing
 */
class SecurityCookieTest extends \PHPUnit_Framework_TestCase
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
        $this->phpCookieManagerMock = $this->getMock(
            \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class,
            ['setPublicCookie'],
            [],
            '',
            false
        );

        $this->cookieMetadataFactoryMock = $this->getMock(
            \Magento\Framework\Stdlib\Cookie\PublicCookieMetadataFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->cookieMetadataMock = $this->getMock(
            \Magento\Framework\Stdlib\Cookie\PublicCookieMetadata::class,
            ['setPath', 'setDuration'],
            [],
            '',
            false
        );

        $this->cookieReaderMock = $this->getMock(
            \Magento\Framework\Stdlib\Cookie\CookieReaderInterface::class,
            ['getCookie'],
            [],
            '',
            false
        );

        $this->backendDataMock = $this->getMock(
            \Magento\Backend\Helper\Data::class,
            [],
            [],
            '',
            false
        );

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

        $this->assertEquals(intval($cookie), $this->model->getLogoutReasonCookie());
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
                intval($status),
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
