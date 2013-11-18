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
 * @package     Magento_Webhook
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webhook\Model;

/**
 * \Magento\Webhook\Model\User
 *
 * @magentoDbIsolation enabled
 */
class UserTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSharedSecret()
    {
        $webapiUserId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Webapi\Model\Acl\User')
            ->setSecret('secret')
            ->save()
            ->getId();
        $user = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Webhook\Model\User', array('webapiUserId' => $webapiUserId));
        $this->assertEquals('secret', $user->getSharedSecret());
    }
}
