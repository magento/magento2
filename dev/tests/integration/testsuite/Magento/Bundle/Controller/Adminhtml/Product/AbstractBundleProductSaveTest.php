<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Controller\Adminhtml\Product;

use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Model\ResourceModel\Option\Collection as OptionCollection;
use Magento\Bundle\Model\ResourceModel\Selection\Collection as SelectionCollection;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Class determine basic logic for bundle product save tests
 */
abstract class AbstractBundleProductSaveTest extends AbstractBackendController
{
    /** @var string */
    protected $productToDelete;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var Config */
    private $eavConfig;

    /** @var ProductResource */
    private $productResource;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
        $this->eavConfig = $this->_objectManager->get(Config::class);
        $this->productResource = $this->_objectManager->get(ProductResource::class);
        $this->productToDelete = $this->getStaticProductData()['sku'];
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        if ($this->productToDelete) {
            $this->productRepository->deleteById($this->productToDelete);
        }

        parent::tearDown();
    }

    /**
     * Retrieve default product attribute set id.
     *
     * @return int
     */
    protected function getDefaultAttributeSetId(): int
    {
        return (int)$this->eavConfig->getEntityType(ProductAttributeInterface::ENTITY_TYPE_CODE)
            ->getDefaultAttributeSetId();
    }

    /**
     * Prepare request
     *
     * @param array $post
     * @param int|null $id
     * @return array
     */
    protected function prepareRequestData(array $post, ?int $id = null): array
    {
        $post = $this->preparePostParams($post);
        $this->setRequestparams($post, $id);

        return $post;
    }

    /**
     * Prepare and assert bundle options
     *
     * @param array $bundleOptions
     * @return void
     */
    protected function assertBundleOptions(array $bundleOptions): void
    {
        $mainProduct = $this->productRepository->get($this->getStaticProductData()['sku'], false, null, true);
        $optionsCollection = $mainProduct->getTypeInstance()->getOptionsCollection($mainProduct);
        $selectionCollection = $mainProduct->getTypeInstance()
            ->getSelectionsCollection($optionsCollection->getAllIds(), $mainProduct);
        $this->assertOptionsData($bundleOptions, $optionsCollection, $selectionCollection);
    }

    /**
     * Prepare post params before dispatch
     *
     * @param array $post
     * @return array
     */
    private function preparePostParams(array $post): array
    {
        $post['product'] = $this->getStaticProductData();
        foreach ($post['bundle_options']['bundle_options'] as &$bundleOption) {
            $bundleOption = $this->prepareOptionByType($bundleOption['type'], $bundleOption);
            $productIdsBySkus = $this->productResource->getProductsIdsBySkus(
                array_column($bundleOption['bundle_selections'], 'sku')
            );
            foreach ($bundleOption['bundle_selections'] as &$bundleSelection) {
                $bundleSelection = $this->prepareSelection($productIdsBySkus, $bundleSelection);
            }
        }

        return $post;
    }

    /**
     * Prepare option params
     *
     * @param string $type
     * @param array $option
     * @return array
     */
    private function prepareOptionByType(string $type, array $option): array
    {
        $option['required'] = '1';
        $option['delete'] = '';
        $option['title'] = $option['title'] ?? $type . ' Option Title';

        return $option;
    }

    /**
     * Prepare selection params
     *
     * @param array $productIdsBySkus
     * @param array $selection
     * @return array
     */
    private function prepareSelection(array $productIdsBySkus, array $selection): array
    {
        $staticData = [
            'price' => '10.000000',
            'selection_qty' => '5.0000',
            'selection_can_change_qty' => '0'
        ];
        $selection['product_id'] = $productIdsBySkus[$selection['sku']];
        $selection = array_merge($selection, $staticData);

        return $selection;
    }

    /**
     * Assert bundle options data
     *
     * @param array $expectedOptions
     * @param OptionCollection $actualOptions
     * @param SelectionCollection $selectionCollection
     * @return void
     */
    private function assertOptionsData(
        array $expectedOptions,
        OptionCollection $actualOptions,
        SelectionCollection $selectionCollection
    ): void {
        $this->assertCount(count($expectedOptions['bundle_options']), $actualOptions);
        foreach ($expectedOptions['bundle_options'] as $expectedOption) {
            $optionToCheck = $actualOptions->getItemByColumnValue('title', $expectedOption['title']);
            $this->assertNotNull($optionToCheck->getId());
            $selectionToCheck = $selectionCollection->getItemsByColumnValue('option_id', $optionToCheck->getId());
            $this->assertCount(count($expectedOption['bundle_selections']), $selectionToCheck);
            $this->assertSelections($expectedOption['bundle_selections'], $selectionToCheck);
            unset($expectedOption['delete'], $expectedOption['bundle_selections']);
            foreach ($expectedOption as $key => $value) {
                $this->assertEquals($value, $optionToCheck->getData($key));
            }
        }
    }

    /**
     * Assert selections data
     *
     * @param array $expectedSelections
     * @param array $actualSelections
     * @return void
     */
    private function assertSelections(array $expectedSelections, array $actualSelections): void
    {
        foreach ($expectedSelections as $expectedSelection) {
            $actualSelectionToCheck = $this->getSelectionByProductSku($expectedSelection['sku'], $actualSelections);
            $this->assertNotNull($actualSelectionToCheck);
            foreach ($expectedSelection as $key => $value) {
                $this->assertEquals($value, $actualSelectionToCheck->getData($key));
            }
        }
    }

    /**
     * Get selection by product sku
     *
     * @param string $sku
     * @param array $actualSelections
     * @return ProductInterface
     */
    private function getSelectionByProductSku(string $sku, array $actualSelections): ProductInterface
    {
        $item = null;
        foreach ($actualSelections as $selection) {
            if ($selection->getSku() === $sku) {
                $item = $selection;
                break;
            }
        }

        return $item;
    }

    /**
     * Set request parameters
     *
     * @param array $post
     * @param int|null $id
     * @return void
     */
    private function setRequestParams(array $post, ?int $id): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $params = ['type' => Type::TYPE_CODE, 'set' => $this->getDefaultAttributeSetId()];
        if ($id) {
            $params['id'] = $id;
        }
        $this->getRequest()->setParams($params);
        $this->getRequest()->setPostValue('product', $post['product']);
        $this->getRequest()->setPostValue('bundle_options', $post['bundle_options']);
    }

    /**
     * Get main product data
     *
     * @return array
     */
    abstract protected function getStaticProductData(): array;
}
