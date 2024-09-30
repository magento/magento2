<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Controller\Adminhtml\System;

use Magento\Store\Model\ResourceModel\Store as StoreResource;
use Magento\Store\Api\Data\StoreInterfaceFactory;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Request\Http as HttpRequest;

/**
 * @magentoAppArea adminhtml
 */
class StoreTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * @var StoreResource
     */
    private $storeResource;

    /**
     * @var StoreInterfaceFactory
     */
    private $storeFactory;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->formKey = $this->_objectManager->get(FormKey::class);
        $this->storeResource = $this->_objectManager->get(StoreResource::class);
        $this->storeFactory = $this->_objectManager->get(StoreInterfaceFactory::class);
        $this->websiteRepository = $this->_objectManager->get(WebsiteRepositoryInterface::class);
    }

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
        $post['form_key'] = $this->formKey->getFormKey();
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($post);
        $this->dispatch('backend/admin/system_store/save');
        //Check that errors was generated and set to session
        $this->assertSessionMessages(
            $this->containsEqual($message),
            MessageInterface::TYPE_ERROR,
            ManagerInterface::class
        );
    }

    /**
     * Save action test.
     * Changing of a default website when a target website doesn't have a default store view.
     *
     * @return void
     * @magentoDataFixture Magento/Store/_files/second_website_with_store_group_and_store.php
     */
    public function testSaveActionChangeDefaultWebsiteThatDoesntHaveDefaultStoreView(): void
    {
        $secondWebsite = $this->websiteRepository->get('test');
        // inactivate default store view of second store
        $secondStore = $this->storeFactory->create();
        $this->storeResource->load($secondStore, 'fixture_second_store', 'code');
        $secondStore->setIsActive(0);
        $this->storeResource->save($secondStore);

        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue([
            'website' => [
                'name' => 'Test Website',
                'code' => 'test',
                'sort_order' => '0',
                'default_group_id' => $secondWebsite->getDefaultGroupId(),
                'is_default' => '1',
                'website_id' => $secondWebsite->getId(),
            ],
            'store_type' => 'website',
            'store_action' => 'edit',
        ],);
        $this->dispatch('backend/admin/system_store/save');
        //Check that errors was generated and set to session
        $this->assertSessionMessages(
            $this->containsEqual('Please enable your Store View before using this Web Site as Default'),
            MessageInterface::TYPE_ERROR,
            ManagerInterface::class
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
