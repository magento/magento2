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
use Magento\Framework\View\Element\Template\Context;

/**
 * This Block will add inline critical css in case dev/css/use_css_critical_path is enabled.
 */
class CriticalCss extends Template
{
    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * @param Context $context
     * @param Repository $assetRepo
     * @param array $data
     */
    public function __construct(
        Context $context,
        Repository $assetRepo,
        array $data = []
    ) {
        $this->assetRepo = $assetRepo;
        parent::__construct($context, $data);
    }

    /**
     * Returns critical css data as string.
     *
     * @return bool|string
     */
    public function getCriticalCssData()
    {
        try {
            $asset = $this->assetRepo->createAsset(
                (string)$this->getFilePath(),
                ['_secure' => 'false']
            );
            $content = $asset->getContent();
        } catch (LocalizedException | NotFoundException $e) {
            $content = '';
        }

        return $content;
    }
}
