<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Dashboard;

class ProductsViewedTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @magentoAppArea adminhtml
     * @magentoDataFixture Magento/Reports/_files/viewed_products.php
     */
    public function testExecute()
    {
        $this->dispatch('backend/admin/dashboard/productsViewed/');

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());

        $actual = $this->getResponse()->getBody();
        $this->assertContains('Simple Product', $actual);
    }
}
