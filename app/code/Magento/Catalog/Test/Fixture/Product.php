<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Fixture;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Option as CustomOption;
use Magento\Catalog\Model\Product\Option\Value as CustomOptionValue;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Fixture\Api\DataMerger;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;

/**
 * Product fixture
 *
 * Usage examples:
 *
 * 1. Create a product with default data
 * <pre>
 *  #[
 *      DataFixture(ProductFixture::class, as: 'product')
 *  ]
 * </pre>
 * 2. Create a product with custom data
 * <pre>
 *  #[
 *      DataFixture(AttributeFixture::class, as: 'attribute')
 *      DataFixture(
 *          ProductFixture::class,
 *          [
 *              'price' => 100,
 *              'custom_attributes' => [['attribute_code' => '$attribute.code$', 'value' => 'Custom Value']],
 *              'extension_attributes' => ['stock_item' => ['is_in_stock' => false]]
 *          ]
 *     )
 *  ]
 * </pre>
 * 3. Create a product with custom options
 * <pre>
 *  #[
 *      DataFixture(
 *          ProductFixture::class,
 *          [
 *              'options' => [
 *                  [
 *                      'type' => ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN,
 *                      'title' => 'Option 1',
 *                      'values' => [
 *                          [ 'title' => 'Option 1 Value 1', 'price' => 2.5, 'sku' => 'option1value1' ],
 *                          [ 'title' => 'Option 1 Value 2', 'price' => 3, 'sku' => 'option1value2' ],
 *                      ]
 *                  ]
 *              ]
 *          ]
 *      ),
 *  ]
 * </pre>
 * 4. Create a product with media gallery entries
 * <pre>
 *  #[
 *      DataFixture(
 *          ProductFixture::class,
 *          [
 *              'media_gallery_entries' => [
 *                  [
 *                     'types' => ['image'],
 *                     'label' => 'Image Label 1',
 *                     'content' => [
 *                         'type' => 'image/png', // image will be auto-generated if not provided
 *                     ],
 *                 ],
 *                 [
 *                     'types' => ['small_image', 'thumbnail'],
 *                     'content' => [
 *                         'type' => 'image/jpeg',
 *                         'base64_encoded_data' => '...', // base64 encoded image data can be provided here
 *                     ],
 *                 ],
 *                 [], // empty array will use all default values and generate an image
 *              ]
 *          ],
 *     )
 *  ];
 * </pre>
 * 5. Update an existing product within specific scope
 * <pre>
 *  #[
 *      DataFixture(StoreFixture::class, as: 'store_view_2'),
 *      DataFixture(ProductFixture::class, as: 'product')
 *      DataFixture(
 *          ProductFixture::class,
 *          ['sku' => '$product.sku$', 'name' => 'name in store view 2', '_update' => true],
 *          scope: 'store_view_2'
 *     )
 *  ]
 * </pre>
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Product implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'id' => null,
        'type_id' => Type::TYPE_SIMPLE,
        'attribute_set_id' => 4,
        'name' => 'Simple Product%uniqid%',
        'sku' => 'simple-product%uniqid%',
        'price' => 10,
        'weight' => 1,
        'visibility' => Visibility::VISIBILITY_BOTH,
        'status' => Status::STATUS_ENABLED,
        'custom_attributes' => [
            'tax_class_id' => '2',
            'special_price' => null,
        ],
        'extension_attributes' => [
            'website_ids' => [1],
            'category_links' => [],
            'stock_item' => [
                'use_config_manage_stock' => true,
                'qty' => 100,
                'is_qty_decimal' => false,
                'is_in_stock' => true,
            ]
        ],
        'product_links' => [],
        'options' => [],
        'media_gallery_entries' => [],
        'tier_prices' => [],
        'created_at' => null,
        'updated_at' => null
    ];

    private const DEFAULT_PRODUCT_LINK_DATA = [
        'sku' => null,
        'type' => 'related',
        'position' => 1,
    ];

    /**
     * @var ServiceFactory
     */
    private $serviceFactory;

    /**
     * @var ProcessorInterface
     */
    private $dataProcessor;

    /**
     * @var DataMerger
     */
    private $dataMerger;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductInterfaceFactory
     */
    private $productFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param ServiceFactory $serviceFactory
     * @param ProcessorInterface $dataProcessor
     * @param DataMerger $dataMerger
     * @param ProductRepositoryInterface $productRepository
     * @param ProductInterfaceFactory|null $productFactory
     * @param DataObjectHelper|null $dataObjectHelper
     */
    public function __construct(
        ServiceFactory $serviceFactory,
        ProcessorInterface $dataProcessor,
        DataMerger $dataMerger,
        ProductRepositoryInterface $productRepository,
        ?ProductInterfaceFactory $productFactory = null,
        ?DataObjectHelper $dataObjectHelper = null
    ) {
        $this->serviceFactory = $serviceFactory;
        $this->dataProcessor = $dataProcessor;
        $this->dataMerger = $dataMerger;
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory ?? ObjectManager::getInstance()->get(ProductInterfaceFactory::class);
        $this->dataObjectHelper = $dataObjectHelper ?? ObjectManager::getInstance()->get(DataObjectHelper::class);
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters. Same format as Product::DEFAULT_DATA. Custom attributes and extension attributes
     *  can be passed directly in the outer array instead of custom_attributes or extension_attributes.
     *
     * Additional fields:
     *  - `_update`: boolean - whether to update product instead of creating a new one
     *
     * @return DataObject|null
     */
    public function apply(array $data = []): ?DataObject
    {
        if (!empty($data['_update'])) {
            $product = $this->productFactory->create();
            unset($data['_update']);
            $this->dataObjectHelper->populateWithArray(
                $product,
                $data,
                ProductInterface::class
            );
            // Add data that are not part of the interface
            $product->addData($this->dataProcessor->process($this, array_diff_key($data, self::DEFAULT_DATA)));
            return $this->productRepository->save($product);
        }
        
        $service = $this->serviceFactory->create(ProductRepositoryInterface::class, 'save');

        return $service->execute(
            [
                'product' => $this->prepareData($data)
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        $service = $this->serviceFactory->create(ProductRepositoryInterface::class, 'deleteById');
        $service->execute(['sku' => $data->getSku()]);
    }

    /**
     * Prepare product data
     *
     * @param array $data
     * @return array
     */
    private function prepareData(array $data): array
    {
        $data = $this->dataMerger->merge(self::DEFAULT_DATA, $data);
        // remove category_links if empty in order for category_ids to be processed if exists
        if (empty($data['extension_attributes']['category_links'])) {
            unset($data['extension_attributes']['category_links']);
        }

        $data['product_links'] = $this->prepareLinksData($data);
        $data['options'] = $this->prepareOptions($data);
        $data['media_gallery_entries'] = $this->prepareMediaGallery($data);

        return $this->dataProcessor->process($this, $data);
    }

    /**
     * Prepare links data
     *
     * @param array $data
     * @return array
     * @throws NoSuchEntityException
     */
    private function prepareLinksData(array $data): array
    {
        $links = [];

        $position = 1;
        foreach ($data['product_links'] as $link) {
            $defaultLinkData = self::DEFAULT_PRODUCT_LINK_DATA;
            $defaultLinkData['position'] = $position;
            $linkData = [];
            if (is_numeric($link)) {
                $product = $this->productRepository->getById($link);
            } elseif (is_string($link)) {
                $product = $this->productRepository->get($link);
            } elseif ($link instanceof ProductInterface) {
                $product = $this->productRepository->get($link->getSku());
            } else {
                $linkData = $link instanceof DataObject ? $link->toArray() : $link;
                $product = $this->productRepository->get($linkData['sku']);
            }

            $linkData += $defaultLinkData;
            $links[] = [
                'sku' => $data['sku'],
                'link_type' => $linkData['type'],
                'linked_product_sku' => $product->getSku(),
                'linked_product_type' =>  $product->getTypeId(),
                'position' => $linkData['position'],
                'extension_attributes' => array_diff_key($linkData, $defaultLinkData),
            ];
            $position++;
        }

        return $links;
    }

    /**
     *
     * Prepare custom option fixtures
     *
     * @param array $data
     * @return array
     */
    private function prepareOptions(array $data): array
    {
        $options = [];
        $default = [
            CustomOption::KEY_PRODUCT_SKU => $data['sku'],
            CustomOption::KEY_TITLE => 'customoption%order%%uniqid%',
            CustomOption::KEY_TYPE => ProductCustomOptionInterface::OPTION_TYPE_FIELD,
            CustomOption::KEY_IS_REQUIRE => true,
            CustomOption::KEY_PRICE => 10.0,
            CustomOption::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
            CustomOption::KEY_SKU => 'customoption%order%%uniqid%',
            CustomOption::KEY_MAX_CHARACTERS => null,
            CustomOption::KEY_SORT_ORDER => 1,
            'values' => null,
        ];
        $defaultValue = [
            CustomOptionValue::KEY_TITLE => 'customoption%order%_%valueorder%%uniqid%',
            CustomOptionValue::KEY_PRICE => 1,
            CustomOptionValue::KEY_PRICE_TYPE => ProductPriceOptionsInterface::VALUE_FIXED,
            CustomOptionValue::KEY_SKU => 'customoption%order%_%valueorder%%uniqid%',
            CustomOptionValue::KEY_SORT_ORDER => 1,
        ];
        $sortOrder = 1;
        foreach ($data['options'] as $item) {
            $option = $item + [CustomOption::KEY_SORT_ORDER => $sortOrder++] + $default;
            $option[CustomOption::KEY_TITLE] = strtr(
                $option[CustomOption::KEY_TITLE],
                ['%order%' => $option[CustomOption::KEY_SORT_ORDER]]
            );
            $option[CustomOption::KEY_SKU] = strtr(
                $option[CustomOption::KEY_SKU],
                ['%order%' => $option[CustomOption::KEY_SORT_ORDER]]
            );
            if (isset($item['values'])) {
                $valueSortOrder = 1;
                $option['values'] = [];
                foreach ($item['values'] as $value) {
                    $value += [CustomOptionValue::KEY_SORT_ORDER => $valueSortOrder++] + $defaultValue;
                    $value[CustomOptionValue::KEY_TITLE] = strtr(
                        $value[CustomOptionValue::KEY_TITLE],
                        [
                            '%order%' => $option[CustomOption::KEY_SORT_ORDER],
                            '%valueorder%' => $value[CustomOptionValue::KEY_SORT_ORDER]
                        ]
                    );
                    $value[CustomOptionValue::KEY_SKU] = strtr(
                        $value[CustomOptionValue::KEY_SKU],
                        [
                            '%order%' => $option[CustomOption::KEY_SORT_ORDER],
                            '%valueorder%' => $value[CustomOptionValue::KEY_SORT_ORDER]
                        ]
                    );
                    $option['values'][] = $value;
                }
            }
            $options[] = $option;
        }

        return $options;
    }

    /**
     * Prepare media gallery entries fixtures
     *
     * @param array $data
     * @return array
     */
    private function prepareMediaGallery(array $data): array
    {
        $mimeTypeExtensionMap = [
            'image/jpeg' => 'jpeg',
            'image/png' => 'png',
        ];
        $default = [
            'id' => null,
            'position' => 1,
            'media_type' => 'image',
            'disabled' => false,
            'label' => 'Image%position%%uniqid%',
            'types' => [
                'image',
                'small_image',
                'thumbnail',
            ],
            'content' => [
                'type' => 'image/jpeg',
                'name' => 'image%position%%uniqid%.%extension%',
                'base64_encoded_data' => '',
            ],
        ];
        $mediaGalleryEntries = [];
        $position = 1;
        foreach ($data['media_gallery_entries'] as $item) {
            $mediaGalleryEntry = $item + ['position' => $position++] + $default;
            //reset types for subsequent images
            $default['types'] = [];
            $placeholders = [
                '%position%' => $mediaGalleryEntry['position'],
                '%extension%' => $mimeTypeExtensionMap[$mediaGalleryEntry['content']['type']]
            ];
            $mediaGalleryEntry['label'] = strtr($mediaGalleryEntry['label'], $placeholders);
            $mediaGalleryEntry['content']['name'] = strtr($mediaGalleryEntry['content']['name'], $placeholders);
            if (empty($mediaGalleryEntry['content']['base64_encoded_data'])) {
                $imageContent = base64_encode($this->generateImage($mediaGalleryEntry['content']['type']));
                $mediaGalleryEntry['content']['base64_encoded_data'] = $imageContent;
            }
            $mediaGalleryEntries[] = $mediaGalleryEntry;
        }
        return $mediaGalleryEntries;
    }

    /**
     * Generate a dummy image
     *
     * @param string $type
     * @return string
     */
    private function generateImage(string $type): string
    {
        ob_start();
        $image = imagecreatetruecolor(1024, 768);
        switch ($type) {
            case 'image/jpeg':
                imagejpeg($image);
                break;
            case 'image/png':
                imagepng($image);
                break;
        }
        $content = ob_get_clean();
        imagedestroy($image);

        return $content;
    }
}
