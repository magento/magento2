<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaGalleryCatalogUi\Ui\Component\Listing\Columns;

use Magento\Catalog\Model\Category\Image;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Thumbnail column for Category grid
 */
class Thumbnail extends Column
{

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Image
     */
    private $categoryImage;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var AssetRepository
     */
    private $assetRepository;

    /**
     * @var string[]
     */
    private $defaultPlaceholder;

    /**
     * Thumbnail constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param StoreManagerInterface $storeManager
     * @param Image $categoryImage
     * @param CategoryRepository $categoryRepository
     * @param AssetRepository $assetRepository
     * @param array $defaultPlaceholder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $storeManager,
        Image $categoryImage,
        CategoryRepository $categoryRepository,
        AssetRepository $assetRepository,
        array $defaultPlaceholder = [],
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->storeManager = $storeManager;
        $this->categoryImage = $categoryImage;
        $this->categoryRepository = $categoryRepository;
        $this->assetRepository = $assetRepository;
        $this->defaultPlaceholder = $defaultPlaceholder;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        $fieldName = $this->getData('name');
        foreach ($dataSource['data']['items'] as & $item) {
            if (isset($item[$fieldName])) {
                $item[$fieldName . '_src'] = $this->getUrl($item[$fieldName]);
                continue;
            }

            if (isset($item['entity_id'])) {
                $src = $this->categoryImage->getUrl(
                    $this->categoryRepository->get($item['entity_id'])
                );

                if (!empty($src)) {
                    $item[$fieldName . '_src'] = $src;
                    continue;
                }
            }

            $item[$fieldName . '_src'] = $this->assetRepository->getUrl($this->defaultPlaceholder['image']);
        }

        return $dataSource;
    }

    /**
     * Get URL for the provided media asset path
     *
     * @param string $path
     * @return string
     * @throws LocalizedException
     */
    private function getUrl(string $path): string
    {
        /** @var Store $store */
        $store = $this->storeManager->getStore();

        return $store->getBaseUrl() . $path;
    }
}
