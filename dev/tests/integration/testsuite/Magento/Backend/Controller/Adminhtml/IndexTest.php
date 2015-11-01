<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml;

/**
 * @magentoAppArea adminhtml
 */
class IndexTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * Check not logged state
     * @covers \Magento\Backend\Controller\Adminhtml\Index\Index::executeInternal
     */
    public function testNotLoggedIndexAction()
    {
        $this->_auth->logout();
        $this->dispatch('backend/admin/index/index');
        $this->assertFalse($this->getResponse()->isRedirect());

        $body = $this->getResponse()->getBody();
        $this->assertSelectCount('form#login-form input#username[type=text]', true, $body);
        $this->assertSelectCount('form#login-form input#login[type=password]', true, $body);
    }

    /**
     * Check logged state
     * @covers \Magento\Backend\Controller\Adminhtml\Index\Index::executeInternal
     * @magentoDbIsolation enabled
     */
    public function testLoggedIndexAction()
    {
        $this->dispatch('backend/admin/index/index');
        $this->assertRedirect();
    }

    /**
     * @covers \Magento\Backend\Controller\Adminhtml\Index\GlobalSearch::executeInternal
     */
    public function testGlobalSearchAction()
    {
        $this->getRequest()->setParam('isAjax', 'true');
        $this->getRequest()->setPostValue('query', 'dummy');
        $this->dispatch('backend/admin/index/globalSearch');

        $actual = $this->getResponse()->getBody();
        $this->assertEquals([], json_decode($actual));
    }
}
