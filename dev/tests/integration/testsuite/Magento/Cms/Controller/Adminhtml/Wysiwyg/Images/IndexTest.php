<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Cms\Controller\Adminhtml\Wysiwyg\Images;

class IndexTest extends \Magento\Backend\Utility\Controller
{
    public function testViewAction()
    {
        $this->dispatch('backend/cms/wysiwyg_images/index/target_element_id/page_content/store/undefined/type/image/');
        $content = $this->getResponse()->getBody();
        $this->assertNotContains('<html', $content);
        $this->assertNotContains('<head', $content);
        $this->assertNotContains('<body', $content);
    }
}
