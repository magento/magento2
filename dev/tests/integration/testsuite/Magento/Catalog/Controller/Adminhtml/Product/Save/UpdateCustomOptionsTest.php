<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product\Save;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory;
use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Base test cases for update product custom options with type "field".
 * Option updating via dispatch product controller action save with updated options data in POST data.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
 */
class UpdateCustomOptionsTest extends AbstractBackendController
{
    /**
     * @var string
     */
    protected $productSku = 'simple';

    /**
     * Test add to product custom option with type "field".
     *
     * @dataProvider \Magento\TestFramework\Catalog\Model\Product\Option\DataProvider\Type\Field::getDataForUpdateOptions
     *
     * @param array $optionData
     * @param array $updateData
     * @return void
     */
    public function testUpdateCustomOptionWithTypeField(array $optionData, array $updateData): void
    {
        $productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
        $optionRepository = $this->_objectManager->get(ProductCustomOptionRepositoryInterface::class);
        $product = $productRepository->get($this->productSku);
        /** @var ProductCustomOptionInterface|Option $option */
        $option = $this->_objectManager->get(ProductCustomOptionInterfaceFactory::class)
            ->create(['data' => $optionData]);
        $option->setProductSku($product->getSku());
        $product->setOptions([$option]);
        $productRepository->save($product);
        $currentProductOptions = $optionRepository->getProductOptions($product);
        $this->assertCount(1, $currentProductOptions);
        /** @var ProductCustomOptionInterface $currentOption */
        $currentOption = reset($currentProductOptions);
        $postData = [
            'product' => [
                'options' => [
                    [
                        'option_id' => $currentOption->getOptionId(),
                        'product_id' => $product->getId(),
                        'type' => $currentOption->getType(),
                        'is_require' => $currentOption->getIsRequire(),
                        'sku' => $currentOption->getSku(),
                        'max_characters' => $currentOption->getMaxCharacters(),
                        'title' => $currentOption->getTitle(),
                        'sort_order' => $currentOption->getSortOrder(),
                        'price' => $currentOption->getPrice(),
                        'price_type' => $currentOption->getPriceType(),
                        'is_use_default' => false,
                    ],
                ],
            ],
        ];

        foreach ($updateData as $methodKey => $newValue) {
            $postData = array_replace_recursive(
                $postData,
                [
                    'product' => [
                        'options' => [
                            0 => [
                                $methodKey => $newValue,
                            ],
                        ],
                    ],
                ]
            );
            $this->getRequest()->setPostValue($postData);
            $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
            $this->dispatch('backend/catalog/product/save/id/' . $product->getEntityId());
            $this->assertSessionMessages(
                $this->containsEqual('You saved the product.'),
                MessageInterface::TYPE_SUCCESS
            );
            $updatedOptions = $optionRepository->getProductOptions($product);
            $this->assertCount(1, $updatedOptions);
            /** @var ProductCustomOptionInterface|Option $updatedOption */
            $updatedOption = reset($updatedOptions);
            $this->assertEquals($newValue, $updatedOption->getDataUsingMethod($methodKey));
            $this->assertEquals($option->getOptionId(), $updatedOption->getOptionId());
            $this->assertNotEquals(
                $option->getDataUsingMethod($methodKey),
                $updatedOption->getDataUsingMethod($methodKey)
            );
        }
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $reflection = new \ReflectionObject($this);
        foreach ($reflection->getProperties() as $property) {
            if (!$property->isStatic() && 0 !== strpos($property->getDeclaringClass()->getName(), 'PHPUnit')) {
                $property->setAccessible(true);
                $property->setValue($this, null);
            }
        }
    }
}
