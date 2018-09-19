<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Source;

use Magento\Framework\App\Utility\Files;
use Magento\Deploy\Package\PackageFileFactory;

/**
 * Collect files eligible for deployment from themes
 */
class Themes implements SourceInterface
{
    /**
     * Source type code
     */
    const TYPE = 'themes';

    /**
     * @var Files
     */
    private $filesUtil;

    /**
     * @var PackageFileFactory
     */
    private $packageFileFactory;

    /**
     * Themes constructor
     *
     * @param Files $filesUtil
     * @param PackageFileFactory $packageFileFactory
     */
    public function __construct(
        Files $filesUtil,
        PackageFileFactory $packageFileFactory
    ) {
        $this->filesUtil = $filesUtil;
        $this->packageFileFactory = $packageFileFactory;
    }

    /**
     * @inheritdoc
     */
    public function get()
    {
        $files = [];
        foreach ($this->filesUtil->getStaticPreProcessingFiles() as $info) {
            list($area, $theme, $locale, $module, $fileName, $fullPath) = $info;
            if (!empty($theme)) {
                $locale = $locale ?: null;
                $params = [
                    'area' => $area,
                    'theme' => $theme,
                    'locale' => $locale,
                    'module' => $module,
                    'fileName' => $fileName,
                    'sourcePath' => $fullPath
                ];
                $files[] = $this->packageFileFactory->create($params);
            }
        }
        return $files;
    }
}
