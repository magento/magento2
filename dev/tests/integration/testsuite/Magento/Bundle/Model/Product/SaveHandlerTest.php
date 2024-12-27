<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Product;

use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Api\Data\LinkInterfaceFactory;
use Magento\Bundle\Api\Data\OptionInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Bundle\Model\Product\SaveHandler
 * The tested class used indirectly
 *
 * @magentoDataFixture Magento/Bundle/_files/product.php
 * @magentoDataFixture Magento/Store/_files/second_website_with_two_stores.php
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 */
class SaveHandlerTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Store
     */
    private $store;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->store = $this->objectManager->create(Store::class);
        /** @var ProductRepositoryInterface $productRepository */
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
    }

    /**
     * Test option title on different stores
     *
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testOptionTitlesOnDifferentStores(): void
    {
        /** @var OptionList $optionList */
        $optionList = $this->objectManager->create(OptionList::class);

        $secondStoreId = $this->store->load('fixture_second_store')->getId();
        $thirdStoreId = $this->store->load('fixture_third_store')->getId();

        $product = $this->productRepository->get('bundle-product', true, $secondStoreId, true);
        $options = $optionList->getItems($product);
        $title = $options[0]->getTitle();
        $newTitle = $title . ' ' . $this->store->load('fixture_second_store')->getCode();
        $options[0]->setTitle($newTitle);
        $extension = $product->getExtensionAttributes();
        $extension->setBundleProductOptions($options);
        $product->setExtensionAttributes($extension);
        $product->save();

        $product = $this->productRepository->get('bundle-product', true, $thirdStoreId, true);
        $options = $optionList->getItems($product);
        $newTitle = $title . ' ' . $this->store->load('fixture_third_store')->getCode();
        $options[0]->setTitle($newTitle);
        $extension = $product->getExtensionAttributes();
        $extension->setBundleProductOptions($options);
        $product->setExtensionAttributes($extension);
        $product->save();

        $product = $this->productRepository->get('bundle-product', false, $secondStoreId, true);
        $options = $optionList->getItems($product);
        $this->assertCount(1, $options);
        $this->assertEquals(
            $title . ' ' . $this->store->load('fixture_second_store')->getCode(),
            $options[0]->getTitle()
        );
    }

    /**
     * Test option link of the same product
     *
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testOptionLinksOfSameProduct(): void
    {
        /** @var OptionList $optionList */
        $optionList = $this->objectManager->create(OptionList::class);
        $product = $this->productRepository->get('bundle-product', true, 0, true);

        //set the first option
        $options = $this->setBundleProductOptionData();
        $extension = $product->getExtensionAttributes();
        $extension->setBundleProductOptions($options);
        $product->setExtensionAttributes($extension);
        $product->save();

        $product = $this->productRepository->get('bundle-product', true, 0, true);
        $options = $optionList->getItems($product);
        $this->assertCount(1, $options);

        //set the second option with same product
        $newOption = $this->setBundleProductOptionData();
        array_push($options, current($newOption));
        $extension = $product->getExtensionAttributes();
        $extension->setBundleProductOptions($options);
        $product->setExtensionAttributes($extension);
        $product->save();
        $this->assertCount(2, $options);

        //remove one option and verify the count
        array_pop($options);
        $extension = $product->getExtensionAttributes();
        $extension->setBundleProductOptions($options);
        $product->setExtensionAttributes($extension);
        $product->save();

        $product = $this->productRepository->get('bundle-product', true, 0, true);
        $options = $optionList->getItems($product);
        $this->assertCount(1, $options);
    }

    /**
     * Set product option link
     *
     * @param $bundleLinks
     * @param $option
     * @return array
     * @throws NoSuchEntityException
     */
    private function setProductLink($bundleLinks, $option): array
    {
        $links = [];
        $options = [];
        if (!empty($bundleLinks)) {
            foreach ($bundleLinks as $linkData) {
                if (!(bool)$linkData['delete']) {
                    /** @var LinkInterface $link */
                    $link = $this->objectManager->create(LinkInterfaceFactory::class)
                        ->create(['data' => $linkData]);
                    $linkProduct = $this->productRepository->getById($linkData['product_id']);
                    $link->setSku($linkProduct->getSku());
                    $link->setQty($linkData['selection_qty']);
                    $link->setPrice($linkData['selection_price_value']);
                    if (isset($linkData['selection_can_change_qty'])) {
                        $link->setCanChangeQuantity($linkData['selection_can_change_qty']);
                    }
                    $links[] = $link;
                }
            }
            $option->setProductLinks($links);
            $options[] = $option;
        }
        return $options;
    }

    /**
     * Set product option
     *
     * @return array
     * @throws NoSuchEntityException
     */
    private function setProductOption(): array
    {
        $options = [];
        $product = $this->productRepository->get('bundle-product', true);
        foreach ($product->getBundleOptionsData() as $optionData) {
            if (!(bool)$optionData['delete']) {
                $option = $this->objectManager->create(OptionInterfaceFactory::class)
                    ->create(['data' => $optionData]);
                $option->setSku($product->getSku());
                $option->setOptionId(null);

                $bundleLinks = $product->getBundleSelectionsData();
                if (!empty($bundleLinks)) {
                    $options = $this->setProductLink(current($bundleLinks), $option);
                }
            }
        }
        return $options;
    }

    /**
     * Set bundle product option data
     *
     * @return array
     * @throws NoSuchEntityException
     */
    private function setBundleProductOptionData(): array
    {
        $options = [];
        $product = $this->productRepository->get('bundle-product', true);
        $simpleProduct = $this->productRepository->get('simple');
        $product->setBundleOptionsData(
            [
                [
                    'title' => 'Bundle Product Items',
                    'default_title' => 'Bundle Product Items',
                    'type' => 'select', 'required' => 1,
                    'delete' => '',
                ],
            ]
        );
        $product->setBundleSelectionsData(
            [
                [
                    [
                        'product_id' => $simpleProduct->getId(),
                        'selection_price_value' => 10,
                        'selection_qty' => 1,
                        'selection_can_change_qty' => 1,
                        'delete' => '',

                    ],
                ],
            ]
        );
        if ($product->getBundleOptionsData()) {
            $options = $this->setProductOption();
        }
        return $options;
    }
}
