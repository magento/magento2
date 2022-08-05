<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\WebapiWorkaround\Override\Config;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\DirSearch;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\File\CollectorInterface;
use Magento\Framework\View\File\Factory as FileFactory;

class FileCollector implements CollectorInterface
{
    /**
     * @var DirSearch
     */
    private $componentDirSearch;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @param DirSearch $dirSearch
     * @param FileFactory $fileFactory
     */
    public function __construct(
        DirSearch $dirSearch,
        FileFactory $fileFactory
    ) {
        $this->componentDirSearch = $dirSearch;
        $this->fileFactory = $fileFactory;
    }

    /**
     * Retrieve files
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @param string $filePath
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return \Magento\Framework\View\File[]
     */
    public function getFiles(ThemeInterface $theme, $filePath)
    {
        $result = [];
        $configFiles = $this->componentDirSearch->collectFilesWithContext(
            ComponentRegistrar::MODULE,
            'Test/Api/_files/' . $filePath
        );
        foreach ($configFiles as $file) {
            $result[] = $this->fileFactory->create($file->getFullPath(), $file->getComponentName(), null, true);
        }
        return $result;
    }
}
