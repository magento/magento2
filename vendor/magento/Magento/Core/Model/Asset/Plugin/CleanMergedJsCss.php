<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Core\Model\Asset\Plugin;

use Magento\Framework\App\Filesystem\DirectoryList;

class CleanMergedJsCss
{
    /**
     * @var \Magento\Core\Helper\File\Storage\Database
     */
    protected $database;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @param \Magento\Core\Helper\File\Storage\Database $database
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Core\Helper\File\Storage\Database $database,
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
