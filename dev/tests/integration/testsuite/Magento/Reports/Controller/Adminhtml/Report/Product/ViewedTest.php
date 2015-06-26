<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Reports\Controller\Adminhtml\Report\Product;

/**
 * @magentoAppArea adminhtml
 */
class ViewedTest extends \Magento\TestFramework\TestCase\AbstractBackendController
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
            'cGVyaW9kX3R5cGU9ZGF5JmZyb209MDIlMkYxJTJGMjAxNSZ0bz0wMiUyRjE2JTJGMjAxNSZzaG93X2VtcHR5X3Jvd3M9MA');
        $actual = $this->getResponse()->getBody();
        $this->assertContains('Product Views Report', $actual);
        $this->assertNotContains('An error occurred while showing the product views report.', $actual);
    }

    public function testExecuteWithError()
    {
        $this->markTestSkipped('MAGETWO-38528');
        $this->dispatch('backend/reports/report_product/viewed/filter/' .
            'cGVyaW9kX3R5cGU9ZGF5JmZyb209NyUyRjElMkY2NyZ0bz1odG1sJTIwZm90bSZzaG93X2VtcHR5X3Jvd3M9MA==');
        $actual = $this->getResponse()->getBody();
        $this->assertEquals('', $actual);
    }
}
