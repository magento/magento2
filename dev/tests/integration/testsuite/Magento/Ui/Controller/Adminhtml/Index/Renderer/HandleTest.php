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
<<<<<<< HEAD
 * Test for \Magento\Ui\Controller\Adminhtml\Index\Render\Handle.
 *
=======
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
 * @magentoAppArea adminhtml
 */
class HandleTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
<<<<<<< HEAD
     * @magentoDataFixture Magento/Customer/_files/customer.php
=======
     * @magentoDataFixture  Magento/Customer/_files/customer.php
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     */
    public function testExecuteWhenUserDoesNotHavePermission()
    {
        Bootstrap::getObjectManager()->configure([
            'preferences' => [
<<<<<<< HEAD
                AuthorizationInterface::class => \Magento\Ui\Model\AuthorizationMock::class,
            ],
=======
                AuthorizationInterface::class => \Magento\Ui\Model\AuthorizationMock::class
            ]
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
<<<<<<< HEAD
     * @magentoDataFixture Magento/Customer/_files/customer.php
=======
     * @magentoDataFixture  Magento/Customer/_files/customer.php
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
