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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Less;

use Magento\Less\PreProcessor\InstructionFactory;

/**
 * LESS instruction preprocessor
 */
class PreProcessor
{
    /**
     * Folder for publication preprocessed less files
     */
    const PUBLICATION_PREFIX_PATH = 'less';

    /**
     * @var \Magento\View\FileSystem
     */
    protected $viewFileSystem;

    /**
     * @var \Magento\Filesystem
     */
    protected $filesystem;

    /**
     * @var InstructionFactory
     */
    protected $instructionFactory;

    /**
     * @var \Magento\Logger
     */
    protected $logger;

    /**
     * @var array
     */
    protected $preProcessors;

    /**
     * @param \Magento\View\FileSystem $viewFileSystem
     * @param \Magento\Filesystem $filesystem
     * @param InstructionFactory $instructionFactory
     * @param \Magento\Logger $logger
     * @param array $preProcessors
     */
    public function __construct(
        \Magento\View\FileSystem $viewFileSystem,
        \Magento\Filesystem $filesystem,
        InstructionFactory $instructionFactory,
        \Magento\Logger $logger,
        array $preProcessors = array()
    ) {
        $this->viewFileSystem = $viewFileSystem;
        $this->filesystem = $filesystem;
        $this->instructionFactory = $instructionFactory;
        $this->logger = $logger;
        $this->preProcessors = $preProcessors;
    }

    /**
     * Instantiate instruction less preprocessors
     *
     * @param array $params
     * @return \Magento\Less\PreProcessorInterface[]
     */
    protected function getLessPreProcessors(array $params)
    {
        $preProcessors = [];
        foreach ($this->preProcessors as $preProcessorClass) {
            $preProcessors[] = $this->instructionFactory->create($preProcessorClass['class'], $params);
        }
        return $preProcessors;
    }

    /**
     * Get base directory with source of less files
     *
     * @return \Magento\Filesystem\Directory\ReadInterface
     */
    protected function getDirectoryRead()
    {
        return $this->filesystem->getDirectoryRead(\Magento\App\Filesystem::ROOT_DIR);
    }

    /**
     * Get directory for publication temporary less files
     *
     * @return \Magento\Filesystem\Directory\WriteInterface
     */
    protected function getDirectoryWrite()
    {
        return $this->filesystem->getDirectoryWrite(\Magento\App\Filesystem::TMP_DIR);
    }

    /**
     * Generate new source path for less file into temporary folder
     *
     * @param string $lessFileSourcePath
     * @return string
     */
    protected function generateNewPath($lessFileSourcePath)
    {
        $sourcePathPrefix = $this->getDirectoryRead()->getAbsolutePath();
        $targetPathPrefix = $this->getDirectoryWrite()->getAbsolutePath() . self::PUBLICATION_PREFIX_PATH . '/';
        return str_replace($sourcePathPrefix, $targetPathPrefix, $lessFileSourcePath);
    }

    /**
     * Process less content throughout all existed instruction preprocessors
     *
     * @param string $lessFilePath
     * @param array $params
     * @return string of saved or original preprocessed less file
     */
    public function processLessInstructions($lessFilePath, $params)
    {
        $lessFileSourcePath = $this->viewFileSystem->getViewFile($lessFilePath, $params);
        $directoryRead = $this->getDirectoryRead();
        $lessContent = $lessSourceContent = $directoryRead->readFile(
            $directoryRead->getRelativePath($lessFileSourcePath)
        );

        foreach ($this->getLessPreProcessors($params) as $processor) {
            $lessContent = $processor->process($lessContent);
        }

        $lessFileTargetPath = $this->generateNewPath($lessFileSourcePath);
        if ($lessFileSourcePath != $lessFileTargetPath && $lessSourceContent != $lessContent) {
            $directoryWrite = $this->getDirectoryWrite();
            $directoryWrite->writeFile($directoryWrite->getRelativePath($lessFileTargetPath), $lessContent);
            return $lessFileTargetPath;
        }

        return $lessFileSourcePath;
    }
}
