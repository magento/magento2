<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MediaGalleryUi\Ui\Component\Listing;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGalleryApi\Api\Data\AssetKeywordsInterface;
use Magento\MediaGalleryApi\Api\Data\KeywordInterface;
use Magento\MediaGalleryApi\Api\GetAssetsKeywordsInterface;
use Psr\Log\LoggerInterface as Logger;

class Provider extends SearchResult
{
    /**
     * @var GetAssetsKeywordsInterface
     */
    private $getAssetKeywords;

    /**
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param GetAssetsKeywordsInterface $getAssetKeywords
     * @param string $mainTable
     * @param null|string $resourceModel
     * @param null|string $identifierName
     * @param null|string $connectionName
     * @throws LocalizedException
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        GetAssetsKeywordsInterface $getAssetKeywords,
        $mainTable = 'media_gallery_asset',
        $resourceModel = null,
        $identifierName = null,
        $connectionName = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $mainTable,
            $resourceModel,
            $identifierName,
            $connectionName
        );
        $this->getAssetKeywords = $getAssetKeywords;
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        $data = parent::getData();
        $keywords = [];
        foreach ($this->_items as $asset) {
            $keywords[$asset->getId()] = array_map(function (AssetKeywordsInterface $assetKeywords) {
                return array_map(function (KeywordInterface $keyword) {
                    return $keyword->getKeyword();
                }, $assetKeywords->getKeywords());
            }, $this->getAssetKeywords->execute([$asset->getId()]));
        }

        /** @var AssetInterface $asset */
        foreach ($data as $key => $asset) {
            $data[$key]['thumbnail_url'] = $asset['path'];
            $data[$key]['content_type'] = strtoupper(str_replace('image/', '', $asset['content_type']));
            $data[$key]['preview_url'] = $asset['path'];
            $data[$key]['keywords'] = isset($keywords[$asset['id']]) ? implode(",", $keywords[$asset['id']]) : '';
            $data[$key]['source'] = empty($asset['source']) ? __('Local') : $asset['source'];
        }
        return $data;
    }
}
