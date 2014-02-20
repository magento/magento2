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
 * @category    Magento
 * @package     Magento_Customer
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Customer\Model;

/**
 * @magentoDataFixture Magento/Customer/_files/customer.php
 */
class SessionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    protected function setUp()
    {
        $this->_customerSession = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Customer\Model\Session');
    }

    public function testLogin()
    {
        $this->markTestSkipped('MAGETWO-18328');
        $oldSessionId = $this->_customerSession->getSessionId();
        $this->assertTrue($this->_customerSession->login('customer@example.com', 'password')); // fixture
        $this->assertTrue($this->_customerSession->isLoggedIn());
        $newSessionId = $this->_customerSession->getSessionId();
        $this->assertNotEquals($oldSessionId, $newSessionId);
    }

    public function testLoginById()
    {
        $this->markTestSkipped('MAGETWO-18328');
        $oldSessionId = $this->_customerSession->getSessionId();
        $this->assertTrue($this->_customerSession->loginById(1)); // fixture
        $this->assertTrue($this->_customerSession->isLoggedIn());
        $newSessionId = $this->_customerSession->getSessionId();
        $this->assertNotEquals($oldSessionId, $newSessionId);
    }
}
