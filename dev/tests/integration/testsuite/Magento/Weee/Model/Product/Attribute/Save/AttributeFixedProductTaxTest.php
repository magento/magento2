<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Weee\Model\Product\Attribute\Save;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\Exception;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation enabled
 * @magentoDataFixture Magento/Weee/_files/fixed_product_attribute.php
 * @magentoDataFixture Magento/Catalog/_files/second_product_simple.php
 */
class AttributeFixedProductTaxTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var string */
    private $attributeCode;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->attributeCode = 'fixed_product_attribute';
    }

    /**
     * @dataProvider fPTProvider
     * @param array $data
     * @param array $expectedData
     * @return void
     */
    public function testSaveProductWithFPTAttribute(array $data, array $expectedData): void
    {
        $product = $this->productRepository->get('simple2');
        $product->addData([$this->attributeCode => $data]);
        $product = $this->productRepository->save($product);
        $this->assertEquals($expectedData, $product->getData($this->attributeCode));
    }

    /**
     * @return array
     */
    public function fPTProvider(): array
    {
        return [
            [
                'data' => [
                    [
                        'region_id' => '0',
                        'country' => 'GB',
                        'val' => '',
                        'value' => '15',
                        'website_id' => '0',
                        'state' => '',
                    ],
                    [
                        'region_id' => '1',
                        'country' => 'US',
                        'val' => '',
                        'value' => '35',
                        'website_id' => '0',
                        'state' => '',
                    ],
                ],
                'expected_data' => [
                    [
                        'website_id' => '0',
                        'country' => 'GB',
                        'state' => '0',
                        'value' => '15.000',
                        'website_value' => 15.0,
                    ],
                    [
                        'website_id' => '0',
                        'country' => 'US',
                        'state' => '0',
                        'value' => '35.000',
                        'website_value' => 35.0
                    ],
                ],
            ],
        ];
    }

    /**
     * @return void
     */
    public function testSaveProductWithFPTAttributeWithDuplicates(): void
    {
        $attributeValues = [
            [
                'region_id' => '0',
                'country' => 'GB',
                'val' => '',
                'value' => '15',
                'website_id' => '0',
                'state' => '',
            ],
            [
                'region_id' => '0',
                'country' => 'GB',
                'val' => '',
                'value' => '15',
                'website_id' => '0',
                'state' => '',
            ],
        ];
        $this->expectException(Exception::class);
        $message = 'Set unique country-state combinations within the same fixed product tax. '
            . 'Verify the combinations and try again.';
        $this->expectExceptionMessage((string)__($message));
        $product = $this->productRepository->get('simple2');
        $product->addData([$this->attributeCode => $attributeValues]);
        $this->productRepository->save($product);
    }
}
