<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryRenditions\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class responsible for providing access to Media Gallery Renditions system configuration.
 */
class Config
{
    private const TABLE_CORE_CONFIG_DATA = 'core_config_data';
    private const XML_PATH_MEDIA_GALLERY_ENABLED = 'system/media_gallery/enabled';
    private const XML_PATH_ENABLED = 'system/media_gallery_renditions/enabled';
    private const XML_PATH_MEDIA_GALLERY_RENDITIONS_WIDTH_PATH = 'system/media_gallery_renditions/width';
    private const XML_PATH_MEDIA_GALLERY_RENDITIONS_HEIGHT_PATH = 'system/media_gallery_renditions/height';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ResourceConnection $resourceConnection
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Check if the media gallery is enabled
     *
     * @return bool
     */
    public function isMediaGalleryEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_MEDIA_GALLERY_ENABLED);
    }

    /**
     * Should the renditions be inserted in the content instead of original image
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED);
    }

    /**
     * Get max width
     *
     * @return int
     */
    public function getWidth(): int
    {
        try {
            return $this->getDatabaseValue(self::XML_PATH_MEDIA_GALLERY_RENDITIONS_WIDTH_PATH);
        } catch (NoSuchEntityException $exception) {
            return (int) $this->scopeConfig->getValue(self::XML_PATH_MEDIA_GALLERY_RENDITIONS_WIDTH_PATH);
        }
    }

    /**
     * Get max height
     *
     * @return int
     */
    public function getHeight(): int
    {
        try {
            return $this->getDatabaseValue(self::XML_PATH_MEDIA_GALLERY_RENDITIONS_HEIGHT_PATH);
        } catch (NoSuchEntityException $exception) {
            return (int) $this->scopeConfig->getValue(self::XML_PATH_MEDIA_GALLERY_RENDITIONS_HEIGHT_PATH);
        }
    }

    /**
     * Get value from database bypassing config cache
     *
     * @param string $path
     * @return int
     * @throws NoSuchEntityException
     */
    private function getDatabaseValue(string $path): int
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(
                [
                    'config' => $this->resourceConnection->getTableName(self::TABLE_CORE_CONFIG_DATA)
                ],
                [
                    'value'
                ]
            )
            ->where('config.path = ?', $path);
        $value = $connection->query($select)->fetchColumn();

        if ($value === false) {
            throw new NoSuchEntityException(
                __(
                    'The config value for %path is not saved to database.',
                    ['path' => $path]
                )
            );
        }

        return (int) $value;
    }
}
