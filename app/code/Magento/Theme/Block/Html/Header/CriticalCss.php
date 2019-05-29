<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Theme\Block\Html\Header;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Asset\File\NotFoundException;

/**
 * This block will add inline critical css
 * in case dev/css/use_css_critical_path is enabled
 */
class CriticalCss extends Template
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
     * @param Template\Context $context
     * @param Repository $assetRepo
     * @param string $filePath
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Repository $assetRepo,
        string $filePath = '',
        array $data = []
    ) {
        $this->assetRepo = $assetRepo;
        $this->filePath = $filePath;
        parent::__construct($context, $data);
    }

    /**
     * Returns critical css data as string.
     *
     * @return bool|string
     * @throws LocalizedException
     */
    public function getCriticalCssData()
    {
        try {
            $asset = $this->assetRepo->createAsset($this->filePath, ['_secure' => 'false']);
            $content = $asset->getContent();
        } catch (LocalizedException | NotFoundException $e) {
            throw new LocalizedException(__("Cannot get critical css file data ", $e->getMessage()));
        };

        return $content;
    }
}
