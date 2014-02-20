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
     * @var \Magento\Less\PreProcessorInterface[]
     */
    protected $preProcessors;

    /**
     * @param \Magento\View\FileSystem $viewFileSystem
     * @param \Magento\Filesystem $filesystem
     * @param InstructionFactory $instructionFactory
     * @param array $preProcessors
     */
    public function __construct(
        \Magento\View\FileSystem $viewFileSystem,
        \Magento\Filesystem $filesystem,
        InstructionFactory $instructionFactory,
        array $preProcessors = array()
    ) {
        $this->viewFileSystem = $viewFileSystem;
        $this->filesystem = $filesystem;
        $this->instructionFactory = $instructionFactory;
        $this->preProcessors = $this->initLessPreProcessors($preProcessors);
    }

    /**
     * Instantiate instruction less preprocessors
     *
     * @param $preProcessors
     * @return \Magento\Less\PreProcessorInterface[]
     */
    protected function initLessPreProcessors($preProcessors)
    {
        $preProcessorsInstances = [];
        foreach ($preProcessors as $preProcessorClass) {
            $preProcessorsInstances[] = $this->instructionFactory->create($preProcessorClass['class']);
        }
        return $preProcessorsInstances;
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
    protected function generatePath($lessFileSourcePath)
    {
        $sourcePathPrefix = $this->getDirectoryRead()->getAbsolutePath();
        $targetPathPrefix = $this->getDirectoryWrite()->getAbsolutePath() . self::PUBLICATION_PREFIX_PATH . '/';
        return str_replace($sourcePathPrefix, $targetPathPrefix, $lessFileSourcePath);
    }

    /**
     * Save pre-processed less content to temporary folder
     *
     * @param string $lessFileSourcePath absolute path to source less file
     * @param string $lessContent
     * @return string absolute path to the pre-processed less file
     */
    protected function saveLessFile($lessFileSourcePath, $lessContent)
    {
        $lessFileTargetPath = $this->generatePath($lessFileSourcePath);
        $directoryWrite = $this->getDirectoryWrite();
        $directoryWrite->writeFile($directoryWrite->getRelativePath($lessFileTargetPath), $lessContent);
        return $lessFileTargetPath;
    }

    /**
     * Process less content throughout all existed instruction preprocessors
     *
     * @param string $lessFilePath
     * @param array $viewParams
     * @return string of saved or original preprocessed less file
     */
    public function processLessInstructions($lessFilePath, $viewParams)
    {
        $lessFileTargetPath = $lessFileSourcePath = $this->viewFileSystem->getViewFile($lessFilePath, $viewParams);
        $directoryRead = $this->getDirectoryRead();
        $lessContent = $lessSourceContent = $directoryRead->readFile(
            $directoryRead->getRelativePath($lessFileSourcePath)
        );

        foreach ($this->preProcessors as $processor) {
            $lessContent = $processor->process(
                $lessContent,
                $viewParams,
                ['parentPath' => $lessFilePath, 'parentAbsolutePath' => $lessFileSourcePath]
            );
        }

        if ($lessSourceContent != $lessContent) {
            $lessFileTargetPath = $this->saveLessFile($lessFileSourcePath, $lessContent);
        }
        return $lessFileTargetPath;
    }
}
