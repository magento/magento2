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

        $this->assertEquals(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//*[@id="add" and @title = "Create Website"]/span[text() = "Create Website"]',
                $response
            )
        );
        $this->assertEquals(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//*[@id="add_group"]',
                $response
            )
        );
        $this->assertEquals(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//*[@id="add_store"]',
                $response
            )
        );
        $this->assertEquals(
            0,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//*[@id = "add" and @class = "disabled"]',
                $response
            )
        );
        $this->assertEquals(
            0,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//*[@id="add_group" and contains(@class,"disabled")]',
                $response
            )
        );
        $this->assertEquals(
            0,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//*[@id="add_store" and contains(@class,"disabled")]',
                $response
            )
        );
    }

    /**
     * @param array $post
     * @param string $message
     * @dataProvider saveActionWithExistCodeDataProvider
     */
    public function testSaveActionWithExistCode($post, $message)
    {
        /** @var $formKey \Magento\Framework\Data\Form\FormKey */
        $formKey = $this->_objectManager->get(\Magento\Framework\Data\Form\FormKey::class);
        $post['form_key'] = $formKey->getFormKey();
        $this->getRequest()->setMethod('POST');
        $this->getRequest()->setPostValue($post);
        $this->dispatch('backend/admin/system_store/save');
        //Check that errors was generated and set to session
        $this->assertSessionMessages(
            $this->containsEqual($message),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR,
            \Magento\Framework\Message\ManagerInterface::class
        );
    }

    /**
     * @return array
     */
    public function saveActionWithExistCodeDataProvider()
    {
        return [
            [
                'post' => [
                    'website' => [
                        'name' => 'base',
                        'code' => 'base',
                        'sort_order' => '',
                        'is_default' => '',
                        'website_id' => '',
                    ],
                    'store_type' => 'website',
                    'store_action' => 'add',
                ],
                'message' => 'Website with the same code already exists.',
            ],
            [
                'post' => [
                    'group' => [
                        'website_id' => '1',
                        'name' => 'default',
                        'code' => 'default',
                        'root_category_id' => '1',
                        'group_id' => '',
                    ],
                    'store_type' => 'group',
                    'store_action' => 'add',
                ],
                'message' => 'Group with the same code already exists.',
            ],
            [
                'post' => [
                    'store' => [
                        'name' => 'default',
                        'code' => 'default',
                        'is_active' => '1',
                        'sort_order' => '',
                        'is_default' => '',
                        'group_id' => '1',
                        'store_id' => '',
                    ],
                    'store_type' => 'store',
                    'store_action' => 'add',
                ],
                'message' => 'Store with the same code already exists.',
            ],
        ];
    }
}
