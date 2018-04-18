<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\System;

/**
 * @magentoAppArea adminhtml
 */
class StoreTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    public function testIndexAction()
    {
        $this->dispatch('backend/admin/system_store/index');

        $response = $this->getResponse()->getBody();

        $this->assertSelectEquals('#add', 'Create Website', 1, $response);
        $this->assertSelectCount('#add_group', 1, $response);
        $this->assertSelectCount('#add_store', 1, $response);

        $this->assertSelectEquals('#add.disabled', 'Create Website', 0, $response);
        $this->assertSelectCount('#add_group.disabled', 0, $response);
        $this->assertSelectCount('#add_store.disabled', 0, $response);
    }

    public function testSaveActionWithExistCode()
    {
        /** @var $formKey \Magento\Framework\Data\Form\FormKey */
        $formKey = $this->_objectManager->get('Magento\Framework\Data\Form\FormKey');
        $post = [
            'form_key' => $formKey->getFormKey(),
            'website' => [
                'name' => 'base',
                'code' => 'base',
                'sort_order' => '',
                'is_default' => '',
                'website_id' => '',
            ],
            'store_type' => 'website',
            'store_action' => 'add',
        ];
        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setPostValue($post);
        $this->dispatch('backend/admin/system_store/save');
        //Check that errors was generated and set to session
        $this->assertSessionMessages(
            $this->contains("Website with the same code already exists."),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR,
            'Magento\Framework\Message\ManagerInterface'
        );
    }
}
