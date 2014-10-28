<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Controller\Adminhtml\System;

/**
 * @magentoAppArea adminhtml
 */
class StoreTest extends \Magento\Backend\Utility\Controller
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
        $post = array(
            'form_key' => $formKey->getFormKey(),
            'website' => array(
                'name' => 'base',
                'code' => 'base',
                'sort_order' => '',
                'is_default' => '',
                'website_id' => ''
            ),
            'store_type' => 'website',
            'store_action' => 'add'
        );
        $this->getRequest()->setServer(array('REQUEST_METHOD' => 'POST'));
        $this->getRequest()->setPost($post);
        $this->dispatch('backend/admin/system_store/save');
        //Check that errors was generated and set to session
        $this->assertSessionMessages(
            $this->contains("Website with the same code already exists."),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR,
            'Magento\Framework\Message\ManagerInterface'
        );
    }
}
