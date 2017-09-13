<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Controller\Adminhtml\Page;

/**
 * Test for checking functionality of \Magento\Cms\Controller\Adminhtml\Page\SaveTest
 *
 * @security-private
 */
class SaveTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @return void
     */
    public function testSaveActionWrongId()
    {
        $postData = [
            'page_id' => 1111111111111111111111111,
            'title'   => 'test_page_title',
        ];
        $this->getRequest()->setPostValue($postData);
        $this->dispatch('backend/cms/page/save');
        $this->assertEquals(302, $this->getResponse()->getHttpResponseCode());
        $this->assertContains(
            'cms/page/index',
            $this->getResponse()->getHeader('Location')->getFieldValue()
        );
        /** @var \Magento\Framework\Message\Collection $messages */
        $messages = $this->_objectManager->get(\Magento\Framework\Message\ManagerInterface::class)->getMessages();
        $this->assertEquals(1, $messages->getCountByType('error'));
        /** @var \Magento\Framework\Message\Error $message */
        $message = $messages->getItemsByType('error')[0];
        $this->assertEquals('This page no longer exists.', $message->getText());
    }
}
