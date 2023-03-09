<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Service model to upload single file in customizations
 */
namespace Magento\Theme\Model\Theme;

use Magento\Framework\View\Design\Theme\Customization\FileInterface;
use Magento\Framework\View\Design\Theme\FileInterface as ThemeFileInterface;
use Magento\Framework\View\Design\ThemeInterface;

class SingleFile
{
    /**
     * @var FileInterface
     */
    protected $_fileService;

    /**
     * @param FileInterface $fileService
     */
    public function __construct(
        FileInterface $fileService
    ) {
        $this->_fileService = $fileService;
    }

    /**
     * Creates or updates custom single file which belong to a selected theme
     *
     * @param ThemeInterface $themeModel
     * @param string $fileContent
     * @return ThemeFileInterface
     */
    public function update(ThemeInterface $themeModel, $fileContent)
    {
        $customFiles = $themeModel->getCustomization()->getFilesByType($this->_fileService->getType());
        $customCss = reset($customFiles);
        if (empty($fileContent) && $customCss) {
            $customCss->delete();
            return $customCss;
        }
        if (!$customCss) {
            $customCss = $this->_fileService->create();
        }
        $customCss->setData('content', $fileContent);
        $customCss->setTheme($themeModel);
        $customCss->save();
        return $customCss;
    }
}
