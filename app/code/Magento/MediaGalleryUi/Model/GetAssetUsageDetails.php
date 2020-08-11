<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Model;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Exception\IntegrationException;
use Magento\MediaContentApi\Api\GetContentByAssetIdsInterface;

/**
 * Provide information on which content asset is used in
 */
class GetAssetUsageDetails
{
    /**
     * @var GetContentByAssetIdsInterface
     */
    private $getContent;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var array
     */
    private $contentTypes;

    /**
     * @param GetContentByAssetIdsInterface $getContent
     * @param UrlInterface $url
     * @param array $contentTypes
     */
    public function __construct(
        GetContentByAssetIdsInterface $getContent,
        UrlInterface $url,
        array $contentTypes = []
    ) {
        $this->getContent = $getContent;
        $this->url = $url;
        $this->contentTypes = $contentTypes;
    }

    /**
     * Provide information on which content asset is used in
     *
     * @param int $id
     * @return array
     * @throws IntegrationException
     */
    public function execute(int $id): array
    {
        $details = [];

        foreach ($this->getUsageByEntities($id) as $type => $entities) {
            $details[] = [
                'name' => $this->getName($type),
                'number' => count($entities),
                'link' => $this->getLinkUrl($type)
            ];
        }

        return $details;
    }

    /**
     * Retrieve the type name from content types configuration
     *
     * @param string $type
     * @return string
     */
    private function getName(string $type): string
    {
        if (isset($this->contentTypes[$type]) && !empty($this->contentTypes[$type]['name'])) {
            return $this->contentTypes[$type]['name'];
        }
        return $type;
    }

    /**
     * Retrieve the type link from content types configuration
     *
     * @param string $type
     * @return string|null
     */
    private function getLinkUrl(string $type): ?string
    {
        if (isset($this->contentTypes[$type]) && !empty($this->contentTypes[$type]['link'])) {
            return $this->url->getUrl($this->contentTypes[$type]['link']);
        }
        return null;
    }

    /**
     * Get used in counts per type
     *
     * @param int $assetId
     * @return int[]
     * @throws IntegrationException
     */
    private function getUsageByEntities(int $assetId): array
    {
        $usage = [];

        foreach ($this->getContent->execute([$assetId]) as $contentIdentity) {
            $id = $contentIdentity->getEntityId();
            $type = $contentIdentity->getEntityType();
            $usage[$type][$id] = isset($usage[$type][$id]) ? $usage[$type][$id]++ : 0;
        }

        return $usage;
    }
}
