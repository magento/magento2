<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Controller\Adminhtml\Product\Initialization\Helper\Plugin;

use Magento\Bundle\Api\Data\OptionInterfaceFactory as OptionFactory;
use Magento\Bundle\Api\Data\LinkInterfaceFactory as LinkFactory;
use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface as ProductRepository;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Framework\App\RequestInterface;

/**
 * Class Bundle
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Bundle
{
    /**
     * @var ProductCustomOptionInterfaceFactory
     */
    protected $customOptionFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var OptionFactory
     */
    protected $optionFactory;

    /**
     * @var LinkFactory
     */
    protected $linkFactory;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * @param RequestInterface $request
     * @param OptionFactory $optionFactory
     * @param LinkFactory $linkFactory
     * @param ProductRepository $productRepository
     * @param StoreManager $storeManager
     * @param ProductCustomOptionInterfaceFactory $customOptionFactory
     */
    public function __construct(
        RequestInterface $request,
        OptionFactory $optionFactory,
        LinkFactory $linkFactory,
        ProductRepository $productRepository,
        StoreManager $storeManager,
        ProductCustomOptionInterfaceFactory $customOptionFactory
    ) {
        $this->request = $request;
        $this->optionFactory = $optionFactory;
        $this->linkFactory = $linkFactory;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->customOptionFactory = $customOptionFactory;
    }

    /**
     * Setting Bundle Items Data to product for further processing
     *
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return \Magento\Catalog\Model\Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function afterInitialize(
        \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject,
        \Magento\Catalog\Model\Product $product
    ) {
        $compositeReadonly = $product->getCompositeReadonly();
        $result['bundle_selections'] = $result['bundle_options'] = [];
        if (isset($this->request->getPost('bundle_options')['bundle_options'])) {
            foreach ($this->request->getPost('bundle_options')['bundle_options'] as $key => $option) {
                if (empty($option['bundle_selections'])) {
                    continue;
                }
                $result['bundle_selections'][$key] = $option['bundle_selections'];
                unset($option['bundle_selections']);
                $result['bundle_options'][$key] = $option;
            }
            if ($result['bundle_selections'] && !$compositeReadonly) {
                $product->setBundleSelectionsData($result['bundle_selections']);
            }

            if ($result['bundle_options'] && !$compositeReadonly) {
                $product->setBundleOptionsData($result['bundle_options']);
            }

            $this->processBundleOptionsData($product);
            $this->processDynamicOptionsData($product);
        } elseif (!$compositeReadonly) {
            $extension = $product->getExtensionAttributes();
            $extension->setBundleProductOptions([]);
            $product->setExtensionAttributes($extension);
        }

        $affectProductSelections = (bool)$this->request->getPost('affect_bundle_product_selections');
        $product->setCanSaveBundleSelections($affectProductSelections && !$compositeReadonly);
        return $product;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function processBundleOptionsData(\Magento\Catalog\Model\Product $product)
    {
        $bundleOptionsData = $product->getBundleOptionsData();
        if (!$bundleOptionsData) {
            return;
        }
        $options = [];
        foreach ($bundleOptionsData as $key => $optionData) {
            if (!empty($optionData['delete'])) {
                continue;
            }

            $option = $this->optionFactory->create(['data' => $optionData]);
            $option->setSku($product->getSku());

            $links = [];
            $bundleLinks = $product->getBundleSelectionsData();
            if (empty($bundleLinks[$key])) {
                continue;
            }

            foreach ($bundleLinks[$key] as $linkData) {
                if (!empty($linkData['delete'])) {
                    continue;
                }
                if (!empty($linkData['selection_id'])) {
                    $linkData['id'] = $linkData['selection_id'];
                }
                $links[] = $this->buildLink($product, $linkData);
            }
            $option->setProductLinks($links);
            $options[] = $option;
        }

        $extension = $product->getExtensionAttributes();
        $extension->setBundleProductOptions($options);
        $product->setExtensionAttributes($extension);
        return;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    protected function processDynamicOptionsData(\Magento\Catalog\Model\Product $product)
    {
        if ((int)$product->getPriceType() !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
            return;
        }

        if ($product->getOptionsReadonly()) {
            return;
        }
        $product->setCanSaveCustomOptions(true);
        $customOptions = $product->getProductOptions();
        if (!$customOptions) {
            return;
        }
        foreach (array_keys($customOptions) as $key) {
            $customOptions[$key]['is_delete'] = 1;
        }
        $newOptions = $product->getOptions();
        foreach ($customOptions as $customOptionData) {
            if ((bool)$customOptionData['is_delete']) {
                continue;
            }
            $customOption = $this->customOptionFactory->create(['data' => $customOptionData]);
            $customOption->setProductSku($product->getSku());
            $newOptions[] = $customOption;
        }
        $product->setOptions($newOptions);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param array $linkData
     *
     * @return \Magento\Bundle\Api\Data\LinkInterface
     */
    private function buildLink(
        \Magento\Catalog\Model\Product $product,
        array $linkData
    ) {
        $link = $this->linkFactory->create(['data' => $linkData]);

        if ((int)$product->getPriceType() !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
            if (array_key_exists('selection_price_value', $linkData)) {
                $link->setPrice($linkData['selection_price_value']);
            }
            if (array_key_exists('selection_price_type', $linkData)) {
                $link->setPriceType($linkData['selection_price_type']);
            }
        }

        $linkProduct = $this->productRepository->getById($linkData['product_id']);
        $link->setSku($linkProduct->getSku());
        $link->setQty($linkData['selection_qty']);

        if (array_key_exists('selection_can_change_qty', $linkData)) {
            $link->setCanChangeQuantity($linkData['selection_can_change_qty']);
        }

        return $link;
    }
}
