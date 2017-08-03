<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Model\Asset\Plugin;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class \Magento\MediaStorage\Model\Asset\Plugin\CleanMergedJsCss
 *
 * @since 2.0.0
 */
class CleanMergedJsCss
{
    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     * @since 2.0.0
     */
    protected $database;

    /**
     * @var \Magento\Framework\Filesystem
     * @since 2.0.0
     */
    protected $filesystem;

    /**
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $database
     * @param \Magento\Framework\Filesystem $filesystem
     * @since 2.0.0
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
     * @param void $result
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function afterCleanMergedJsCss(\Magento\Framework\View\Asset\MergeService $subject, $result)
    {
        /** @var \Magento\Framework\Filesystem\Directory\ReadInterface $pubStaticDirectory */
        $pubStaticDirectory = $this->filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW);
        $mergedDir = $pubStaticDirectory->getAbsolutePath() . '/'
            . \Magento\Framework\View\Asset\Merged::getRelativeDir();
        $this->database->deleteFolder($mergedDir);
    }
}
