<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Model\AssetDetailsProvider;

use Magento\Framework\Exception\IntegrationException;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGalleryUi\Model\AssetDetailsProviderInterface;

/**
 * Provide asset type
 */
class Type implements AssetDetailsProviderInterface
{
    /**
     * @var array
     */
    private $types;

    /**=
     * @param array $types
     */
    public function __construct(array $types = [])
    {
        $this->types = $types;
    }

    /**
     * Provide asset type
     *
     * @param AssetInterface $asset
     * @return array
     * @throws IntegrationException
     */
    public function execute(AssetInterface $asset): array
    {
        return [
            'title' => __('Type'),
            'value' => $this->getImageTypeByContentType($asset->getContentType()),
        ];
    }

    /**
     * Return image type by content type
     *
     * @param string $contentType
     * @return string
     */
    private function getImageTypeByContentType(string $contentType): string
    {
        $type = current(explode('/', $contentType));

        return isset($this->types[$type]) ? $this->types[$type] : 'Asset';
    }
}
