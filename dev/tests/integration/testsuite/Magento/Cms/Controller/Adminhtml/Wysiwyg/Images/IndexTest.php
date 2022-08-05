<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Controller\Adminhtml\Wysiwyg\Images;

class IndexTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    public function testViewAction()
    {
        $this->dispatch('backend/cms/wysiwyg_images/index/target_element_id/page_content/store/undefined/type/image/');
        $content = $this->getResponse()->getBody();
        $this->assertStringNotContainsString('<html', $content);
        $this->assertStringNotContainsString('<head', $content);
        $this->assertStringNotContainsString('<body', $content);
    }
}
