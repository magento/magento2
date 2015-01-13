<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
