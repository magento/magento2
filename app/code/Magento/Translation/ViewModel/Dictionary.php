<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\ViewModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Asset\File\NotFoundException;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Translation\Model\Js\Config as JsConfig;

/**
 * View model responsible for getting translate dictionary file content for the layout.
 */
class Dictionary implements ArgumentInterface
{
    /**
     * @var AssetRepository
     */
    private $assetRepo;

    /**
     * @param AssetRepository $assetRepo
     */
    public function __construct(
        AssetRepository $assetRepo
    ) {
        $this->assetRepo = $assetRepo;
    }

    /**
     * Get translation dictionary file content.
     *
     * @return string
     */
    public function getTranslationDictionary(): string
    {
        return '[]';
        try {
            $asset = $this->assetRepo->createAsset(JsConfig::DICTIONARY_FILE_NAME);
            $content = $asset->getContent();
        } catch (LocalizedException | NotFoundException $e) {
            $content = '';
        }

        return $content;
    }
}
