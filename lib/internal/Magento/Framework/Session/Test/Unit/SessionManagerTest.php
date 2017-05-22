<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// @codingStandardsIgnoreStart
namespace Magento\Framework\Session\Test\Unit {
    // @codingStandardsIgnoreEnd

    /**
     * Test SessionManager
     *
     */
    class SessionManagerTest extends \PHPUnit_Framework_TestCase
    {
        const SESSION_USE_ONLY_COOKIES = 'session.use_only_cookies';
        const SESSION_USE_ONLY_COOKIES_ENABLE = '1';

        /**
         * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
         */
        private $objectManager;

        /**
         * @var \Magento\Framework\Session\SessionManager
         */
        private $sessionManager;

        /**
         * @var \Magento\Framework\Session\Config\ConfigInterface | \PHPUnit_Framework_MockObject_MockObject
         */
        private $mockSessionConfig;

        /**
         * @var \Magento\Framework\Stdlib\CookieManagerInterface | \PHPUnit_Framework_MockObject_MockObject
         */
        private $mockCookieManager;

        /**
         * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory | \PHPUnit_Framework_MockObject_MockObject
         */
        private $mockCookieMetadataFactory;

        /**
         * @var bool
         */
        public static $isIniSetInvoked;

        protected function setUp()
        {
            global $mockPHPFunctions;
            $mockPHPFunctions = true;
            require_once __DIR__ . '/_files/mock_ini_set.php';
            require_once __DIR__ . '/_files/mock_session_regenerate_id.php';

            $mockPHPFunctions = true;
            $this->mockSessionConfig = $this->getMockBuilder(\Magento\Framework\Session\Config\ConfigInterface::class)
                ->disableOriginalConstructor()
                ->getMock();
            $this->mockCookieManager = $this->getMock(\Magento\Framework\Stdlib\CookieManagerInterface::class);
            $this->mockCookieMetadataFactory = $this->getMockBuilder(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
            )
                ->disableOriginalConstructor()
                ->getMock();
            $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
            $arguments = [
                'sessionConfig' => $this->mockSessionConfig,
                'cookieManager' => $this->mockCookieManager,
                'cookieMetadataFactory' => $this->mockCookieMetadataFactory,
            ];
            $this->sessionManager = $this->objectManager->getObject(
                \Magento\Framework\Session\SessionManager::class,
                $arguments
            );
        }

        public function testSessionManagerConstructor()
        {
            global $mockPHPFunctions;
            self::$isIniSetInvoked = false;
            $this->objectManager->getObject(\Magento\Framework\Session\SessionManager::class);
            $this->assertTrue(SessionManagerTest::$isIniSetInvoked);
            $this->assertTrue($mockPHPFunctions);
        }

        protected function tearDown()
        {
            global $mockPHPFunctions;
            $mockPHPFunctions = false;
        }
    }
}
