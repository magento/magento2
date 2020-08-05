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
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\MediaGalleryApi\Api\GetAssetsByIdsInterface;
use Magento\Cms\Helper\Wysiwyg\Images;
use Magento\Cms\Model\Wysiwyg\Images\Storage;

/**
 * Asset filter
 */
class Asset extends Select
{
    /**
     * @var BookmarkManagementInterface
     */
    private $bookmarkManagement;

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
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param FilterBuilder $filterBuilder
     * @param FilterModifier $filterModifier
     * @param OptionSourceInterface $optionsProvider
     * @param GetContentByAssetIdsInterface $getContentIdentities
     * @param BookmarkManagementInterface $bookmarkManagement
     * @param GetAssetsByIdsInterface $getAssetsByIds
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
        BookmarkManagementInterface $bookmarkManagement,
        GetAssetsByIdsInterface $getAssetsByIds,
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
        $this->bookmarkManagement = $bookmarkManagement;
        $this->getAssetsByIds = $getAssetsByIds;
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
        $assetIds = [];
        $bookmarks = $this->bookmarkManagement->loadByNamespace($this->context->getNameSpace())->getItems();
        foreach ($bookmarks as $bookmark) {
            if ($bookmark->getIdentifier() === 'current') {
                $applied = $bookmark->getConfig()['current']['filters']['applied'];
                if (isset($applied[$this->getName()])) {
                    $assetIds[] = $applied[$this->getName()];
                }
            }
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

        $this->wrappedComponent = $this->uiComponentFactory->create(
            $this->getName(),
            parent::COMPONENT,
            [
                'context' => $this->getContext(),
                'options' => $options
            ]
        );

        $this->wrappedComponent->prepare();
        $jsConfig = array_replace_recursive(
            $this->getJsConfig($this->wrappedComponent),
            $this->getJsConfig($this)
        );
        $this->setData('js_config', $jsConfig);

        $this->setData(
            'config',
            array_replace_recursive(
                (array)$this->wrappedComponent->getData('config'),
                (array)$this->getData('config')
            )
        );

        $this->applyFilter();

        parent::prepare();
    }

    /**
     * Apply filter
     *
     * @return void
     */
    public function applyFilter()
    {
        if (isset($this->filterData[$this->getName()])) {
            $ids = is_array($this->filterData[$this->getName()])
                ? $this->filterData[$this->getName()]
                : [$this->filterData[$this->getName()]];
            $filter = $this->filterBuilder->setConditionType('in')
                    ->setField($this->_data['config']['identityColumn'])
                    ->setValue($this->getEntityIdsByAsset($ids))
                    ->create();

            $this->getContext()->getDataProvider()->addFilter($filter);
        }
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
