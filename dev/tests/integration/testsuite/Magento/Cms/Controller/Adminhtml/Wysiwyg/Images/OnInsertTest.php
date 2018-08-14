<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Controller\Adminhtml\Wysiwyg\Images;

use Magento\Cms\Helper\Wysiwyg\Images;
use Magento\Framework\Data\Form\FormKey;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Provide tests for OnInsert controller.
 * @magentoAppArea adminhtml
 */
class OnInsertTest extends AbstractBackendController
{
    /**
     * Test OnIsert with turned on static urls in catalog.
     *
     * @magentoConfigFixture admin_store cms/wysiwyg/use_static_urls_in_catalog 1
     * @return void
     */
    public function testExecuteWhithStaticUrls()
    {
        $this->prepareRequest();
        $this->dispatch('backend/cms/wysiwyg_images/onInsert');
        $this->assertRegExp('/pub\/media\/wysiwyg\/testFilename/', $this->getResponse()->getBody());
    }

    /**
     * Test OnIsert with turned off static urls in catalog.
     *
     * @magentoConfigFixture admin_store cms/wysiwyg/use_static_urls_in_catalog 0
     * @return void
     */
    public function testExecuteWhithoutStaticUrls()
    {
        $this->prepareRequest();
        $this->dispatch('backend/cms/wysiwyg_images/onInsert');
        $this->assertRegExp('/cms\/wysiwyg\/directive\/___directive/', $this->getResponse()->getBody());
    }

    /**
     * Set necessary post data into request.
     *
     * @return void
     */
    private function prepareRequest()
    {
        $this->getRequest()->setParams(
            [
                'key' => 'testKey',
                'isAjax' => 'true',
                'filename' => Bootstrap::getObjectManager()->get(Images::class)->idEncode('testFilename'),
                'node' => 'root',
                'store' => '',
                'as_is' => '0',
                'form_key' => Bootstrap::getObjectManager()->get(FormKey::class)->getFormKey(),
            ]
        );
    }
}
