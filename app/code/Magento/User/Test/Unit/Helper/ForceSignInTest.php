<?php
/************************************************************************
 *
 * Copyright 2025 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\User\Test\Unit\Helper;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Security\Model\ResourceModel\AdminSessionInfo;
use PHPUnit\Framework\TestCase;
use Magento\User\Helper\ForceSignIn;

/**
 * Revoke user token
 */
class ForceSignInTest extends TestCase
{
    /**
     * @var ForceSignIn
     */
    private $forceSignIn;

    /**
     * @var AdminSessionInfo
     */
    private $adminSessionInfo;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->adminSessionInfo = $this->getMockBuilder(AdminSessionInfo::class)
         ->disableOriginalConstructor()
         ->getMock();

        $objectManager = new ObjectManager($this);
        $this->forceSignIn = $objectManager->getObject(
            ForceSignIn::class,
            [
                'adminSessionInfo' => $this->adminSessionInfo
            ]
        );
    }

    /**
     * Update admin session status
     *
     * @return void
     * @throws LocalizedException
     */
    public function testUpdateAdminSessionStatus()
    {
        $this->adminSessionInfo->expects($this->any())
            ->method('updateStatusByUserId')
            ->with(0, 1, [1], [], null)
            ->willReturnSelf();
        $this->forceSignIn->updateAdminSessionStatus(1);
    }

    /**
     * Throw exception for admin session
     *
     * @throws LocalizedException
     */
    public function testExceptionUpdateAdminSessionStatus()
    {
        $this->expectException(LocalizedException::class);
        $this->adminSessionInfo->expects($this->any())
            ->method('updateStatusByUserId')
            ->with(0, 1, [1], [], null)
            ->willReturnSelf();
        $this->forceSignIn->updateAdminSessionStatus(0);
    }
}
