<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryUi\Ui\Component\Listing\Filters;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\MediaContentApi\Api\GetContentByAssetIdsInterface;
use Magento\Ui\Component\Filters\FilterModifier;
use Magento\Ui\Component\Filters\Type\Select;
use Magento\MediaGalleryApi\Api\GetAssetsByIdsInterface;
use Magento\Cms\Helper\Wysiwyg\Images;
use Magento\Cms\Model\Wysiwyg\Images\Storage;
use Magento\Ui\Api\BookmarkManagementInterface;

/**
 * Asset filter
 */
class Asset extends Select
{
    /**
     * @var GetContentByAssetIdsInterface
     */
    private $getContentIdentities;

    /**
     * @var GetAssetsByIdsInterface
     */
    private $getAssetsByIds;

    /**
     * @var Images
     */
    private $images;

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var BookmarkManagementInterface
     */
    private $bookmarkManagement;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param FilterBuilder $filterBuilder
     * @param FilterModifier $filterModifier
     * @param OptionSourceInterface $optionsProvider
     * @param GetContentByAssetIdsInterface $getContentIdentities
     * @param GetAssetsByIdsInterface $getAssetsByIds
     * @param BookmarkManagementInterface $bookmarkManagement
     * @param Images $images
     * @param Storage $storage
     * @param array $components
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        FilterBuilder $filterBuilder,
        FilterModifier $filterModifier,
        OptionSourceInterface $optionsProvider = null,
        GetContentByAssetIdsInterface $getContentIdentities,
        GetAssetsByIdsInterface $getAssetsByIds,
        BookmarkManagementInterface $bookmarkManagement,
        Images $images,
        Storage $storage,
        array $components = [],
        array $data = []
    ) {
        $this->uiComponentFactory = $uiComponentFactory;
        $this->filterBuilder = $filterBuilder;
        parent::__construct(
            $context,
            $uiComponentFactory,
            $filterBuilder,
            $filterModifier,
            $optionsProvider,
            $components,
            $data
        );
        $this->getContentIdentities = $getContentIdentities;
        $this->getAssetsByIds = $getAssetsByIds;
        $this->bookmarkManagement = $bookmarkManagement;
        $this->images = $images;
        $this->storage = $storage;
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        $options = [];
        $assetIds = $this->getAssetIds();

        if (empty($assetIds)) {
            parent::prepare();
            return;
        }

        $assets = $this->getAssetsByIds->execute($assetIds);

        foreach ($assets as $asset) {
            $assetPath = $this->storage->getThumbnailUrl($this->images->getStorageRoot() . $asset->getPath());
            $options[] = [
                'value' => (string) $asset->getId(),
                'label' => $asset->getTitle(),
                'src' => $assetPath
            ];
        }

        $this->optionsProvider = $options;
        parent::prepare();
    }

    /**
     * Get asset ids from filterData or from bookmarks
     */
    private function getAssetIds(): array
    {
        $assetIds = [];

        if (isset($this->filterData[$this->getName()])) {
            $assetIds = $this->filterData[$this->getName()];

            if (!is_array($assetIds)) {
                $assetIds = $this->stringToArray($assetIds);
            }

            return $assetIds;
        }

        $bookmark = $this->bookmarkManagement->getByIdentifierNamespace(
            'current',
            $this->context->getNameSpace()
        );

        if ($bookmark === null) {
            return $assetIds;
        }

        $applied = $bookmark->getConfig()['current']['filters']['applied'];

        if (isset($applied[$this->getName()])) {
            $assetIds = $applied[$this->getName()];
        }

        if (!is_array($assetIds)) {
            $assetIds = $this->stringToArray($assetIds);
        }

        return $assetIds;
    }

    /**
     * Converts string array from url-applier to array
     *
     * @param string $string
     */
    private function stringToArray(string $string): array
    {
        return explode(',', str_replace(['[', ']'], '', $string));
    }

    /**
     * Apply filter
     *
     * @return void
     */
    public function applyFilter()
    {
        if (!isset($this->filterData[$this->getName()])) {
            return;
        }

        $assetIds = $this->filterData[$this->getName()];
        if (!is_array($assetIds)) {
            $assetIds = $this->stringToArray($assetIds);
        }

        $filter = $this->filterBuilder->setConditionType('in')
            ->setField($this->_data['config']['identityColumn'])
            ->setValue($this->getEntityIdsByAsset($assetIds))
            ->create();
        $this->getContext()->getDataProvider()->addFilter($filter);
    }

    /**
     * Return entity ids by assets ids.
     *
     * @param array $ids
     */
    private function getEntityIdsByAsset(array $ids): string
    {
        if (!empty($ids)) {
            $categoryIds = [];
            $data = $this->getContentIdentities->execute($ids);
            foreach ($data as $identity) {
                if ($identity->getEntityType() === $this->_data['config']['entityType']) {
                    $categoryIds[] = $identity->getEntityId();
                }
            }
            return implode(',', $categoryIds);
        }
        return '';
    }
}
