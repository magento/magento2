<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Page\Config\Metadata;

use Magento\Framework\View\Asset\Repository as AssetRepository;

/**
 * Class MsApplicationTileImage
 *
 * Returns the URL for page `msapplication-TileImage` meta
 */
class MsApplicationTileImage
{
    /**#@+
     * Constant of asset name
     */
    const META_NAME = 'msapplication-TileImage';

    /**
     * @var AssetRepository
     */
    private $assetRepo;

    /**
     * @param AssetRepository $assetRepo
     */
    public function __construct(AssetRepository $assetRepo)
    {
        $this->assetRepo = $assetRepo;
    }

    /**
     * Get asset URL from given metadata content
     *
     * @param string $content
     *
     * @return string
     */
    public function getUrl(string $content): string
    {
        if (!parse_url($content, PHP_URL_SCHEME)) {
            return $this->assetRepo->getUrl($content);
        }

        return $content;
    }
}
