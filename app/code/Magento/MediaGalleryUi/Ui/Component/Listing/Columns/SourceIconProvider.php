<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Ui\Component\Listing\Columns;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\Store;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Source icon url provider
 */
class SourceIconProvider extends Column
{
    /**
     * @var array
     */
    private $sourceIcons;

    /**
     * @var AssetRepository
     */
    private $assetRepository;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param AssetRepository $assetRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param array $components
     * @param array $data
     * @param array $sourceIcons
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        AssetRepository $assetRepository,
        ScopeConfigInterface $scopeConfig,
        array $components = [],
        array $data = [],
        array $sourceIcons = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->assetRepository = $assetRepository;
        $this->scopeConfig = $scopeConfig;
        $this->sourceIcons = $sourceIcons;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items']) && is_iterable($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')] = $item[$this->getData('name')]
                    ? $this->getSourceIconUrl($item[$this->getData('name')])
                    : null;
            }
        }

        return $dataSource;
    }

    /**
     * Construct source icon url based on the source code matching
     *
     * @param string $sourceName
     *
     * @return string|null
     */
    private function getSourceIconUrl(string $sourceName): ?string
    {
        return isset($this->sourceIcons[$sourceName])
            ? $this->assetRepository->getUrlWithParams(
                $this->sourceIcons[$sourceName],
                ['_secure' => $this->isSecure()]
            )
            : null;
    }

    /**
     * Check if store use secure connection
     *
     * @return bool
     */
    private function isSecure(): bool
    {
        return $this->scopeConfig->isSetFlag(Store::XML_PATH_SECURE_IN_ADMINHTML);
    }
}
