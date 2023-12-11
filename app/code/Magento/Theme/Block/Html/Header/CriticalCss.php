<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Theme\Block\Html\Header;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Asset\File\NotFoundException;

/**
 * This ViewModel will add inline critical css in case dev/css/use_css_critical_path is enabled.
 */
class CriticalCss implements ArgumentInterface
{
    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * @var $filePath
     */
    private $filePath;

    /**
     * @param Repository $assetRepo
     * @param string $filePath
     */
    public function __construct(
        Repository $assetRepo,
        string $filePath = ''
    ) {
        $this->assetRepo = $assetRepo;
        $this->filePath = $filePath;
    }

    /**
     * Returns critical css data as string.
     *
     * @return bool|string
     */
    public function getCriticalCssData()
    {
        try {
            $asset = $this->assetRepo->createAsset($this->filePath, ['_secure' => 'false']);
            $content = $asset->getContent();
        } catch (LocalizedException | NotFoundException $e) {
            $content = '';
        }

        return $content;
    }
}
