<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute;

use Magento\Backend\Model\Session;
use Magento\Catalog\Model\CategoryFactory;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * @covers \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Validate::execute
 * @magentoAppArea adminhtml
 */
class ValidateTest extends AbstractBackendController
{
    /**
     * @param array $attributes Request parameter.
     * @dataProvider validateActionDataProvider
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     * @magentoDbIsolation disabled
     */
    public function testValidateActionWithMassUpdate(array $attributes): void
    {
        $session = $this->_objectManager->get(Session::class);
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
    public static function validateActionDataProvider(): array
    {
        return [
            [
                'attributes' => [
                    'name'              => 'Name',
                    'description'       => 'Description',
                    'short_description' => 'Short Description',
                    'price'             => '512',
                    'weight'            => '16',
                    'meta_title'        => 'Meta Title',
                    'meta_keyword'      => 'Meta Keywords',
                    'meta_description'  => 'Meta Description',
                ],
            ],
        ];
    }
}
