<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Controller\Adminhtml\Index\Renderer;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\AuthorizationInterface;

/**
 * Test for \Magento\Ui\Controller\Adminhtml\Index\Render\Handle.
 *
 * @magentoAppArea adminhtml
 */
class HandleTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testExecuteWhenUserDoesNotHavePermission()
    {
        Bootstrap::getObjectManager()->configure([
            'preferences' => [
                AuthorizationInterface::class => \Magento\Ui\Model\AuthorizationMock::class,
            ],
        ]);
        $this->getRequest()->setParam('handle', 'customer_index_index');
        $this->getRequest()->setParam('namespace', 'customer_listing');
        $this->getRequest()->setParam('sorting%5Bfield%5D', 'entity_id');
        $this->getRequest()->setParam('paging%5BpageSize%5D', 20);
        $this->getRequest()->setParam('isAjax', 1);
        $this->dispatch('backend/mui/index/render_handle');
        $output = $this->getResponse()->getBody();
        $this->assertEmpty($output, 'The acl restriction wasn\'t applied properly');
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testExecuteWhenUserHasPermission()
    {
        $this->getRequest()->setParam('handle', 'customer_index_index');
        $this->getRequest()->setParam('namespace', 'customer_listing');
        $this->getRequest()->setParam('sorting%5Bfield%5D', 'entity_id');
        $this->getRequest()->setParam('paging%5BpageSize%5D', 20);
        $this->getRequest()->setParam('isAjax', 1);
        $this->dispatch('backend/mui/index/render_handle');
        $output = $this->getResponse()->getBody();
        $this->assertNotEmpty($output, 'The acl restriction wasn\'t applied properly');
    }
}
