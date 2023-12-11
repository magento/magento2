<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

// @codingStandardsIgnoreStart
namespace {
    $mockPHPFunctions = false;
}

namespace Magento\Framework\Session\Test\Unit {
    use Magento\Framework\Session\Config\ConfigInterface;
    use Magento\Framework\Session\SessionManager;
    use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
    use Magento\Framework\Stdlib\CookieManagerInterface;
    use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
    use PHPUnit\Framework\MockObject\MockObject;
    use PHPUnit\Framework\TestCase;

    // @codingStandardsIgnoreEnd

    /**
     * Test SessionManager
     *
     */
    class SessionManagerTest extends TestCase
    {
        const SESSION_USE_ONLY_COOKIES = 'session.use_only_cookies';
        const SESSION_USE_ONLY_COOKIES_ENABLE = '1';

        /**
         * @var ObjectManager
         */
        private $objectManager;

        /**
         * @var SessionManager
         */
        private $sessionManager;

        /**
         * @var ConfigInterface|MockObject
         */
        private $mockSessionConfig;

        /**
         * @var CookieManagerInterface|MockObject
         */
        private $mockCookieManager;

        /**
         * @var CookieMetadataFactory|MockObject
         */
        private $mockCookieMetadataFactory;

        /**
         * @var bool
         */
        public static $isIniSetInvoked;

        protected function setUp(): void
        {
            $this->markTestSkipped('To be fixed in MAGETWO-34751');
            global $mockPHPFunctions;
            require_once __DIR__ . '/_files/mock_ini_set.php';
            require_once __DIR__ . '/_files/mock_session_regenerate_id.php';

            $mockPHPFunctions = true;
            $this->mockSessionConfig = $this->getMockBuilder(ConfigInterface::class)
                ->disableOriginalConstructor()
                ->getMockForAbstractClass();
            $this->mockCookieManager = $this->getMockForAbstractClass(CookieManagerInterface::class);
            $this->mockCookieMetadataFactory = $this->getMockBuilder(
                CookieMetadataFactory::class
            )
                ->disableOriginalConstructor()
                ->getMock();
            $this->objectManager = new ObjectManager($this);
            $arguments = [
                'sessionConfig' => $this->mockSessionConfig,
                'cookieManager' => $this->mockCookieManager,
                'cookieMetadataFactory' => $this->mockCookieMetadataFactory,
            ];
            $this->sessionManager = $this->objectManager->getObject(
                SessionManager::class,
                $arguments
            );
        }

        public function testSessionManagerConstructor()
        {
            self::$isIniSetInvoked = false;
            $this->objectManager->getObject(SessionManager::class);
            $this->assertTrue(SessionManagerTest::$isIniSetInvoked);
        }
    }
}
