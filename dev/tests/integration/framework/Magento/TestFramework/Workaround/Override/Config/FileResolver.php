<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Workaround\Override\Config;

use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\View\File\CollectorInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\Filesystem\Directory\ReadFactory;

class FileResolver implements FileResolverInterface
{
    /**
     * @var CollectorInterface
     */
    private $baseFiles;

    /**
     * @var DesignInterface
     */
    private $design;

    /**
     * @var ReadFactory
     */
    private $readFactory;

    /**
     * @param CollectorInterface $baseFiles
     * @param DesignInterface $design
     * @param ReadFactory $readFactory
     */
    public function __construct(
        CollectorInterface $baseFiles,
        DesignInterface $design,
        ReadFactory $readFactory
    ) {
        $this->baseFiles = $baseFiles;
        $this->design = $design;
        $this->readFactory = $readFactory;
    }

    /**
     * @inheritdoc
     */
    public function get($filename, $scope): array
    {
        $result = [];

        $files = $this->baseFiles->getFiles($this->design->getDesignTheme(), $filename);
        foreach ($files as $file) {
            $fullFileName = $file->getFileName();
            $fileDir = dirname($fullFileName);
            $fileName = basename($fullFileName);
            $dirRead = $this->readFactory->create($fileDir);
            $result[$fullFileName] = $dirRead->readFile($fileName);
        }

        return $result;
    }
}
