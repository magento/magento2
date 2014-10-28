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
namespace Magento\Reports\Controller\Adminhtml\Report\Product;

/**
 * @magentoAppArea adminhtml
 */
class ViewedTest extends \Magento\Backend\Utility\Controller
{
    public function testExecute()
    {
        $this->dispatch('backend/reports/report_product/viewed/');
        $actual = $this->getResponse()->getBody();
        $this->assertContains('Product Views Report', $actual);
    }

    public function testExecuteWithoutError()
    {
        $this->dispatch('backend/reports/report_product/viewed/filter/' .
            'cGVyaW9kX3R5cGU9ZGF5JmZyb209NDY0NjQmdG89NDY0NjQ2JnNob3dfZW1wdHlfcm93cz0w/');
        $actual = $this->getResponse()->getBody();
        $this->assertContains('Product Views Report', $actual);
        $this->assertNotContains('An error occurred while showing the product views report.', $actual);
    }

    public function testExecuteWithError()
    {
        $this->dispatch('backend/reports/report_product/viewed/filter/' .
            'cGVyaW9kX3R5cGU9ZGF5JmZyb209NyUyRjElMkY2NyZ0bz1odG1sJTIwZm90bSZzaG93X2VtcHR5X3Jvd3M9MA==');
        $actual = $this->getResponse()->getBody();

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var $messageManager \Magento\Framework\Message\Manager */
        $messageManager = $objectManager->get('Magento\Framework\Message\Manager');

        $this->assertContains(
            'An error occurred while showing the product views report.',
            (string)$messageManager->getMessages()->getLastAddedMessage()->getText()
        );

        $this->assertEquals('', $actual);
    }
}
