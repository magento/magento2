<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Model\Asset\Plugin;

use Magento\Framework\App\Filesystem\DirectoryList;

class CleanMergedJsCss
{
    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $database;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $database
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magento\MediaStorage\Helper\File\Storage\Database $database,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->database = $database;
        $this->filesystem = $filesystem;
    }

    /**
     * Clean files in database on cleaning merged assets
     *
     * @param \Magento\Framework\View\Asset\MergeService $subject
     * @param callable $proceed
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCleanMergedJsCss(\Magento\Framework\View\Asset\MergeService $subject, \Closure $proceed)
    {
        $proceed();

        /** @var \Magento\Framework\Filesystem\Directory\ReadInterface $pubStaticDirectory */
        $pubStaticDirectory = $this->filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW);
        $mergedDir = $pubStaticDirectory->getAbsolutePath() . '/'
            . \Magento\Framework\View\Asset\Merged::getRelativeDir();
        $this->database->deleteFolder($mergedDir);
    }
}
