<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Test\Unit\Plugin;

use Exception;
use Magento\AdminAdobeIms\Plugin\SetEmptyPasswordForUserPlugin;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\User\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SetEmptyPasswordForUserPluginTest extends TestCase
{
    private const PASSWORD = 'randomString';

    /**
     * @var SetEmptyPasswordForUserPlugin
     */
    private $plugin;

    /**
     * @var ImsConfig|MockObject
     */
    private $imsConfigMock;

    /**
     * @var User|MockObject
     */
    private $userMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManagerHelper($this);

        $this->userMock = $this->createMock(User::class);
        $this->imsConfigMock = $this->createMock(ImsConfig::class);

        $this->plugin = $objectManagerHelper->getObject(
            SetEmptyPasswordForUserPlugin::class,
            [
                'imsConfig' => $this->imsConfigMock,
            ]
        );
    }

    /**
     * Plugin should return used password when AdminAdobeIms is Disabled
     *
     * @return void
     * @throws Exception
     */
    public function testAfterGetPasswordReturnsValueWhenModuleIsDisabled(): void
    {
        $this->imsConfigMock
            ->expects($this->once())
            ->method('enabled')
            ->willReturn(false);

        $this->assertEquals(self::PASSWORD, $this->plugin->afterGetPassword($this->userMock, self::PASSWORD));
    }

    /**
     * Plugin should return empty string when given password is null
     *
     * @return void
     * @throws Exception
     */
    public function testAfterGetPasswordReturnsEmptyStringWhenModuleIsEnabledAndPasswordIsNull(): void
    {
        $this->imsConfigMock
            ->expects($this->once())
            ->method('enabled')
            ->willReturn(true);

        $this->assertEquals('', $this->plugin->afterGetPassword($this->userMock, null));
    }

    /**
     * Plugin should return given password when module is enabled and password is not null
     *
     * @return void
     * @throws Exception
     */
    public function testAfterGetPasswordReturnsValueStringWhenModuleIsEnabledAndPasswordIsNotNull(): void
    {
        $this->imsConfigMock
            ->expects($this->once())
            ->method('enabled')
            ->willReturn(true);

        $this->assertEquals(
            self::PASSWORD,
            $this->plugin->afterGetPassword($this->userMock, self::PASSWORD)
        );
    }
}
