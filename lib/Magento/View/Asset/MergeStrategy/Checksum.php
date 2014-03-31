<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\View\Asset\MergeStrategy;

/**
 * Merge strategy representing the following: merged file is being recreated if and only if file does not exist
 * or meta-file does not exist or checksums do not match
 */
class Checksum implements \Magento\View\Asset\MergeStrategyInterface
{
    /**
     * Strategy
     *
     * @var \Magento\View\Asset\MergeStrategyInterface
     */
    protected $strategy;

    /**
     * Filesystem
     *
     * @var \Magento\App\Filesystem
     */
    protected $filesystem;

    /**
     * Constructor
     *
     * @param \Magento\View\Asset\MergeStrategyInterface $strategy
     * @param \Magento\App\Filesystem $filesystem
     */
    public function __construct(
        \Magento\View\Asset\MergeStrategyInterface $strategy,
        \Magento\App\Filesystem $filesystem
    ) {
        $this->strategy = $strategy;
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function mergeFiles(array $publicFiles, $destinationFile, $contentType)
    {
        $directory = $this->filesystem->getDirectoryWrite(\Magento\App\Filesystem::PUB_DIR);
        $mergedMTimeFile = $directory->getRelativePath($destinationFile . '.dat');

        // Check whether we have already merged these files
        $filesMTimeData = '';
        foreach ($publicFiles as $file) {
            $filesMTimeData .= $directory->stat($directory->getRelativePath($file))['mtime'];
        }
        if (!($directory->isExist(
            $destinationFile
        ) && $directory->isExist(
            $mergedMTimeFile
        ) && strcmp(
            $filesMTimeData,
            $directory->readFile($mergedMTimeFile)
        ) == 0)
        ) {
            $this->strategy->mergeFiles($publicFiles, $destinationFile, $contentType);
            $directory->writeFile($mergedMTimeFile, $filesMTimeData);
        }
    }
}
