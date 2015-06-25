<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Action;

/**
 * @magentoAppArea adminhtml
 */
class AttributeTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @covers \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save::execute
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testSaveActionRedirectsSuccessfully()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var $session \Magento\Backend\Model\Session */
        $session = $objectManager->get('Magento\Backend\Model\Session');
        $session->setProductIds([1]);

        $this->dispatch('backend/catalog/product_action_attribute/save/store/0');

        $this->assertEquals(302, $this->getResponse()->getHttpResponseCode());
        /** @var \Magento\Backend\Model\UrlInterface $urlBuilder */
        $urlBuilder = $objectManager->get('Magento\Framework\UrlInterface');

        /** @var \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper */
        $attributeHelper = $objectManager->get('Magento\Catalog\Helper\Product\Edit\Action\Attribute');
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
     * @covers \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Validate::execute
     *
     * @dataProvider validateActionDataProvider
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     */
    public function testValidateActionWithMassUpdate($attributes)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var $session \Magento\Backend\Model\Session */
        $session = $objectManager->get('Magento\Backend\Model\Session');
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
}
