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
namespace Magento\Webhook\Block\Adminhtml\Subscription\Grid\Renderer;

/**
 * \Magento\Webhook\Block\Adminhtml\Subscription\Grid\Renderer\Action
 *
 * @magentoAppArea adminhtml
 */
class ActionTest extends \PHPUnit_Framework_TestCase
{
    public function testRender()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $grid = $objectManager->create('Magento\Webhook\Block\Adminhtml\Subscription\Grid\Renderer\Action');

        /** @var \Magento\Webhook\Model\Subscription $subscriptionRow */
        $subscriptionRow = $objectManager->create('Magento\Webhook\Model\Subscription');

        $subscriptionRow->setStatus(\Magento\Webhook\Model\Subscription::STATUS_ACTIVE);
        $this->assertTrue(strpos($grid->render($subscriptionRow), 'Revoke') !== false);

        $subscriptionRow->setStatus(\Magento\Webhook\Model\Subscription::STATUS_INACTIVE);
        $this->assertTrue(strpos($grid->render($subscriptionRow), 'Activate') !== false);
        $this->assertTrue(strpos($grid->render($subscriptionRow), 'activateSubscription') !== false);

        $subscriptionRow->setStatus(\Magento\Webhook\Model\Subscription::STATUS_REVOKED);
        $this->assertTrue(strpos($grid->render($subscriptionRow), 'Activate') !== false);

        $invalidStatus = -1;
        $subscriptionRow->setStatus($invalidStatus);
        $this->assertEquals('', $grid->render($subscriptionRow));
    }
}
