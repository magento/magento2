<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\User\Test\Unit\Model\Backend\Config;

use Magento\User\Model\Backend\Config\ObserverConfig;
use Magento\Backend\App\ConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit Test for \Magento\User\Model\Backend\Config\ObserverConfig class
 *
 * Class \Magento\User\Test\Unit\Model\Backend\Config\ObserverConfigTest
 */
class ObserverConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Config path for lockout threshold
     */
    private const XML_ADMIN_SECURITY_LOCKOUT_THRESHOLD = 'admin/security/lockout_threshold';

    /**
     * Config path for password change is forced or not
     */
    private const XML_ADMIN_SECURITY_PASSWORD_IS_FORCED = 'admin/security/password_is_forced';

    /**
     * Config path for password lifetime
     */
    private const XML_ADMIN_SECURITY_PASSWORD_LIFETIME = 'admin/security/password_lifetime';

    /**
     * Config path for maximum lockout failures
     */
    private const XML_ADMIN_SECURITY_LOCKOUT_FAILURES = 'admin/security/lockout_failures';

    /** @var ObserverConfig */
    private $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ConfigInterface
     */
    private $backendConfigMock;

    /**
     * Set environment for test
     */
    protected function setUp(): void
    {
        $this->backendConfigMock = $this->getMockForAbstractClass(ConfigInterface::class);

        $objectManager = new ObjectManagerHelper($this);
        $this->model = $objectManager->getObject(
            ObserverConfig::class,
            [
                'backendConfig' => $this->backendConfigMock
            ]
        );
    }

    /**
     * Test when admin password lifetime = 0 days
     */
    public function testIsLatestPasswordExpiredWhenNoAdminLifeTime()
    {
        $this->backendConfigMock->expects(self::any())->method('getValue')
            ->with(self::XML_ADMIN_SECURITY_PASSWORD_LIFETIME)
            ->willReturn('0');
        $this->assertFalse($this->model->_isLatestPasswordExpired([]));
    }

    /**
     * Test when admin password lifetime = 2 days
     */
    public function testIsLatestPasswordExpiredWhenHasAdminLifeTime()
    {
        $this->backendConfigMock->expects(self::any())->method('getValue')
            ->with(self::XML_ADMIN_SECURITY_PASSWORD_LIFETIME)
            ->willReturn('2');
        $this->assertTrue($this->model->_isLatestPasswordExpired(['last_updated' => 1571428052]));
    }

    /**
     * Test when security lockout threshold = 100 minutes
     */
    public function testGetAdminLockThreshold()
    {
        $this->backendConfigMock->expects(self::any())->method('getValue')
            ->with(self::XML_ADMIN_SECURITY_LOCKOUT_THRESHOLD)
            ->willReturn('100');
        $this->assertEquals(6000, $this->model->getAdminLockThreshold());
    }

    /**
     * Test when password change force is true
     */
    public function testIsPasswordChangeForcedTrue()
    {
        $this->backendConfigMock->expects(self::any())->method('getValue')
            ->with(self::XML_ADMIN_SECURITY_PASSWORD_IS_FORCED)
            ->willReturn('1');
        $this->assertTrue($this->model->isPasswordChangeForced());
    }

    /**
     * Test when password change force is false
     */
    public function testIsPasswordChangeForcedFalse()
    {
        $this->backendConfigMock->expects(self::any())->method('getValue')
            ->with(self::XML_ADMIN_SECURITY_PASSWORD_IS_FORCED)
            ->willReturn('0');
        $this->assertFalse($this->model->isPasswordChangeForced());
    }

    /**
     * Test when admin password lifetime = 2 days
     */
    public function testGetAdminPasswordLifetime()
    {
        $this->backendConfigMock->expects(self::any())->method('getValue')
            ->with(self::XML_ADMIN_SECURITY_PASSWORD_LIFETIME)
            ->willReturn('2');
        $this->assertEquals(172800, $this->model->getAdminPasswordLifetime());
    }

    /**
     * Test when max failures = 5 (times)
     */
    public function testGetMaxFailures()
    {
        $this->backendConfigMock->expects(self::any())->method('getValue')
            ->with(self::XML_ADMIN_SECURITY_LOCKOUT_FAILURES)
            ->willReturn('5');
        $this->assertEquals(5, $this->model->getMaxFailures());
    }
}
