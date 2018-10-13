<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml;

use Magento\Framework\App\Request\Http as HttpRequest;

/**
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class IndexTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * Check not logged state
     * @covers \Magento\Backend\Controller\Adminhtml\Index\Index::execute
     */
    public function testNotLoggedIndexAction()
    {
        $this->_auth->logout();
        $this->dispatch('backend/admin/index/index');
        /** @var $backendUrlModel \Magento\Backend\Model\UrlInterface */
        $backendUrlModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Backend\Model\UrlInterface::class
        );
        $backendUrlModel->turnOffSecretKey();
        $url = $backendUrlModel->getUrl('admin');
        $this->assertRedirect($this->stringStartsWith($url));
    }

    /**
     * Check logged state
     * @covers \Magento\Backend\Controller\Adminhtml\Index\Index::execute
     *
     */
    public function testLoggedIndexAction()
    {
        $this->dispatch('backend/admin/index/index');
        $this->assertRedirect();
    }

    /**
     * @covers \Magento\Backend\Controller\Adminhtml\Index\GlobalSearch::execute
     */
    public function testGlobalSearchAction()
    {
        $this->getRequest()->setParam('isAjax', 'true');
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue('query', 'dummy');
        $this->dispatch('backend/admin/index/globalSearch');

        $actual = $this->getResponse()->getBody();
        $this->assertEquals([], json_decode($actual));
    }
}
