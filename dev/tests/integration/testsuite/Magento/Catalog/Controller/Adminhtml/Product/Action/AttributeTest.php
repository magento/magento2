<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Action;

use Magento\Framework\App\Request\Http as HttpRequest;

/**
 * @magentoAppArea adminhtml
 */
class AttributeTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @covers \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save::execute
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation disabled
     */
    public function testSaveActionRedirectsSuccessfully()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var $session \Magento\Backend\Model\Session */
        $session = $objectManager->get(\Magento\Backend\Model\Session::class);
        $session->setProductIds([1]);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);

        $this->dispatch('backend/catalog/product_action_attribute/save/store/0');

        $this->assertEquals(302, $this->getResponse()->getHttpResponseCode());
        /** @var \Magento\Backend\Model\UrlInterface $urlBuilder */
        $urlBuilder = $objectManager->get(\Magento\Framework\UrlInterface::class);

        /** @var \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper */
        $attributeHelper = $objectManager->get(\Magento\Catalog\Helper\Product\Edit\Action\Attribute::class);
        $expectedUrl = $urlBuilder->getUrl(
            'catalog/product/index',
            ['store' => $attributeHelper->getSelectedStoreId()]
        );
        $isRedirectPresent = false;
        foreach ($this->getResponse()->getHeaders() as $header) {
            if ($header->getFieldName() === 'Location' && strpos($header->getFieldValue(), $expectedUrl) === 0) {
                $isRedirectPresent = true;
            }
        }

        $this->assertTrue($isRedirectPresent);
    }

    /**
     * @covers \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save::execute
     *
     * @dataProvider saveActionVisibilityAttrDataProvider
     * @param array $attributes
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation disabled
     */
    public function testSaveActionChangeVisibility($attributes)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $repository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ProductRepository::class
        );
        $product = $repository->get('simple');
        $product->setOrigData();
        $product->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE);
        $product->save();

        /** @var $session \Magento\Backend\Model\Session */
        $session = $objectManager->get(\Magento\Backend\Model\Session::class);
        $session->setProductIds([$product->getId()]);
        $this->getRequest()->setParam('attributes', $attributes);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);

        $this->dispatch('backend/catalog/product_action_attribute/save/store/0');
        /** @var \Magento\Catalog\Model\Category $category */
        $categoryFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Catalog\Model\CategoryFactory::class
        );
        /** @var \Magento\Catalog\Block\Product\ListProduct $listProduct */
        $listProduct = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Catalog\Block\Product\ListProduct::class
        );

        $category = $categoryFactory->create()->load(2);
        $layer = $listProduct->getLayer();
        $layer->setCurrentCategory($category);
        $productCollection = $layer->getProductCollection();
        $productItem = $productCollection->getFirstItem();
        $this->assertEquals($session->getProductIds(), [$productItem->getId()]);
    }

    /**
     * @param array $attributes Request parameter.
     *
     * @covers \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Validate::execute
     *
     * @dataProvider validateActionDataProvider
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     * @magentoDbIsolation disabled
     */
    public function testValidateActionWithMassUpdate($attributes)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var $session \Magento\Backend\Model\Session */
        $session = $objectManager->get(\Magento\Backend\Model\Session::class);
        $session->setProductIds([1, 2]);

        $this->getRequest()->setParam('attributes', $attributes);

        $this->dispatch('backend/catalog/product_action_attribute/validate/store/0');

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());

        $response = $this->getResponse()->getBody();
        $this->assertJson($response);
        $data = json_decode($response, true);
        $this->assertArrayHasKey('error', $data);
        $this->assertFalse($data['error']);
        $this->assertCount(1, $data);
    }

    /**
     * Data Provider for validation
     *
     * @return array
     */
    public function validateActionDataProvider()
    {
        return [
            [
                'arguments' => [
                    'name'              => 'Name',
                    'description'       => 'Description',
                    'short_description' => 'Short Description',
                    'price'             => '512',
                    'weight'            => '16',
                    'meta_title'        => 'Meta Title',
                    'meta_keyword'      => 'Meta Keywords',
                    'meta_description'  => 'Meta Description',
                ],
            ]
        ];
    }

    /**
     * Data Provider for save with visibility attribute
     *
     * @return array
     */
    public function saveActionVisibilityAttrDataProvider()
    {
        return [
            ['arguments' => ['visibility' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH]],
            ['arguments' => ['visibility' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG]]
        ];
    }
}
