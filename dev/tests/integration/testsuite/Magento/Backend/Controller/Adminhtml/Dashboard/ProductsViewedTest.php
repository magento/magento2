<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Dashboard;

class ProductsViewedTest extends \Magento\Backend\Utility\Controller
{
    public function testExecute()
    {
        $this->dispatch('backend/admin/dashboard/productsViewed/');
        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());

        $actual = $this->getResponse()->getBody();
        $this->assertContains('dashboard-item-content', $actual);
    }
}
