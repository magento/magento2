<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
// @codingStandardsIgnoreStart
namespace {
    $mockPHPFunctions = false;
}

namespace Magento\Framework\Session {
    // @codingStandardsIgnoreEnd

    /**
     * Mock ini_set global function
     *
     * @param string $varName
     * @param string $newValue
     * @return bool|string
     */
    function ini_set($varName, $newValue)
    {
        global $mockPHPFunctions;
        if ($mockPHPFunctions) {
            SessionManagerTest::$isIniSetInvoked = true;
            SessionManagerTest::assertSame(SessionManagerTest::SESSION_USE_ONLY_COOKIES, $varName);
            SessionManagerTest::assertSame(SessionManagerTest::SESSION_USE_ONLY_COOKIES_ENABLE, $newValue);
            return true;
        }
        return call_user_func_array('\ini_set', func_get_args());
    }

    /**
     * Mock session_regenerate_id to fail if false is passed
     *
     * @param bool $var
     * @return bool
     */
    function session_regenerate_id($var)
    {
        global $mockPHPFunctions;
        if ($mockPHPFunctions) {
            SessionManagerTest::assertTrue($var);
            return true;
        }
        return call_user_func_array('\session_regenerate_id', func_get_args());
    }
   
    /**
     * Test SessionManager
     *
     */
    class SessionManagerTest extends \PHPUnit_Framework_TestCase
    {
        const SESSION_USE_ONLY_COOKIES = 'session.use_only_cookies';
        const SESSION_USE_ONLY_COOKIES_ENABLE = '1';

        /**
         * @var \Magento\TestFramework\Helper\ObjectManager
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
         * @var \Magento\Framework\Stdlib\CookieManager | \PHPUnit_Framework_MockObject_MockObject
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

        public function setUp()
        {
            global $mockPHPFunctions;
            $mockPHPFunctions = true;
            $this->mockSessionConfig = $this->getMockBuilder('\Magento\Framework\Session\Config\ConfigInterface')
                ->disableOriginalConstructor()
                ->getMock();
            $this->mockCookieManager = $this->getMockBuilder('\Magento\Framework\Stdlib\CookieManager')
                ->disableOriginalConstructor()
                ->getMock();
            $this->mockCookieMetadataFactory = $this->getMockBuilder(
                'Magento\Framework\Stdlib\Cookie\CookieMetadataFactory'
            )
                ->disableOriginalConstructor()
                ->getMock();
            $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
            $arguments = [
                'sessionConfig' => $this->mockSessionConfig,
                'cookieManager' => $this->mockCookieManager,
                'cookieMetadataFactory' => $this->mockCookieMetadataFactory
            ];
            $this->sessionManager = $this->objectManager->getObject(
                'Magento\Framework\Session\SessionManager',
                $arguments
            );
        }

        public function testSessionManagerConstructor()
        {
            self::$isIniSetInvoked = false;
            $this->objectManager->getObject('Magento\Framework\Session\SessionManager');
            $this->assertTrue(SessionManagerTest::$isIniSetInvoked);
        }
    }
}
