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
 * @package     Magento_Rss
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Rss\Controller;

/**
 * @magentoAppArea adminhtml
 */
class OrderTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * Reuse URI for "new" action
     */
    const NEW_ORDER_URI = 'backend/rss/order/new';

    public function testNewActionAuthorizationFailed()
    {
        $this->dispatch(self::NEW_ORDER_URI);
        $this->assertHeaderPcre('Http/1.1', '/^401 Unauthorized$/');
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testNewAction()
    {
        $this->getRequest()->setServer(array(
            'PHP_AUTH_USER' => \Magento\TestFramework\Bootstrap::ADMIN_NAME,
            'PHP_AUTH_PW' => \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD
        ));
        $this->dispatch(self::NEW_ORDER_URI);
        $this->assertHeaderPcre('Content-Type', '/text\/xml/');
        $this->assertContains('#100000001', $this->getResponse()->getBody());
    }

    public function testNotLoggedIn()
    {
        $this->dispatch(self::NEW_ORDER_URI);
        $this->assertHeaderPcre('Http/1.1', '/^401 Unauthorized$/');
    }

    /**
     * @param string $login
     * @param string $password
     * @dataProvider invalidAccessDataProvider
     * @magentoDataFixture Magento/User/_files/dummy_user.php
     */
    public function testInvalidAccess($login, $password)
    {
        $this->getRequest()->setServer(array('PHP_AUTH_USER' => $login, 'PHP_AUTH_PW' => $password));
        $this->dispatch(self::NEW_ORDER_URI);
        $this->assertHeaderPcre('Http/1.1', '/^401 Unauthorized$/');
    }

    /**
     * @return array
     */
    public function invalidAccessDataProvider()
    {
        return array(
            'no login' => array('', \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD),
            'no password' => array(\Magento\TestFramework\Bootstrap::ADMIN_NAME, ''),
            'no login and password' => array('', ''),
            'user with inappropriate ACL' => array('dummy_username', 'dummy_password1'),
        );
    }
}
