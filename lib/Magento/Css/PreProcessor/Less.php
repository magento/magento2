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

namespace Magento\Css\PreProcessor;

/**
 * Css pre-processor less
 */
class Less implements \Magento\View\Asset\PreProcessor\PreProcessorInterface
{
    /**
     * @var \Magento\View\FileSystem
     */
    protected $viewFileSystem;

    /**
     * @var \Magento\Less\PreProcessor
     */
    protected $lessPreProcessor;

    /**
     * @var \Magento\Css\PreProcessor\AdapterInterface
     */
    protected $adapter;

    /**
     * @param \Magento\View\FileSystem $viewFileSystem
     * @param \Magento\Less\PreProcessor $lessPreProcessor
     * @param AdapterInterface $adapter
     */
    public function __construct(
        \Magento\View\FileSystem $viewFileSystem,
        \Magento\Less\PreProcessor $lessPreProcessor,
        \Magento\Css\PreProcessor\AdapterInterface $adapter
    ) {
        $this->viewFileSystem = $viewFileSystem;
        $this->lessPreProcessor = $lessPreProcessor;
        $this->adapter = $adapter;
    }

    /**
     * @param string $filePath
     * @param array $params
     * @param \Magento\Filesystem\Directory\WriteInterface $targetDirectory
     * @param null|string $sourcePath
     * @return string
     */
    public function process($filePath, $params, $targetDirectory, $sourcePath = null)
    {
        // if css file has being already discovered/prepared by previous pre-processor
        if ($sourcePath) {
            return $sourcePath;
        }

        // TODO: if css file is already exist. May compare modification time of .less and .css files here.
        $sourcePath = $this->viewFileSystem->getViewFile($filePath, $params);

        $lessFilePath = str_replace('.css', '.less', $filePath);
        $preparedLessFileSourcePath = $this->lessPreProcessor->processLessInstructions($lessFilePath, $params);
        $cssContent = $this->adapter->process($preparedLessFileSourcePath);

        // doesn't matter where exact file has been found, we use original file identifier
        // see \Magento\View\Publisher::_buildPublishedFilePath() for details
        $targetDirectory->writeFile($filePath, $cssContent);
        return $targetDirectory->getAbsolutePath($filePath);
    }
}
