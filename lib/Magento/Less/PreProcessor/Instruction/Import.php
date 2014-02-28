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

namespace Magento\Less\PreProcessor\Instruction;

use Magento\Less\PreProcessorInterface;
use Magento\Less\PreProcessor;
use Magento\View;

/**
 * Less @import instruction preprocessor
 */
class Import implements PreProcessorInterface
{
    /**
     * Pattern of @import less instruction
     */
    const REPLACE_PATTERN =
        '#@import\s+(\((?P<type>\w+)\)\s+)?[\'\"](?P<path>(?![/\\\]|\w:[/\\\])[^\"\']+)[\'\"]\s*?(?P<media>.*?);#';

    /**
     * @var PreProcessor\File\FileList
     */
    protected $fileList;

    /**
     * Pre-processor error handler
     *
     * @var PreProcessor\ErrorHandlerInterface
     */
    protected $errorHandler;

    /**
     * Related file
     *
     * @var View\RelatedFile
     */
    protected $relatedFile;

    /**
     * @param View\RelatedFile $relatedFile
     * @param PreProcessor\ErrorHandlerInterface $errorHandler
     * @param PreProcessor\File\FileList $fileList
     */
    public function __construct(
        View\RelatedFile $relatedFile,
        PreProcessor\ErrorHandlerInterface $errorHandler,
        PreProcessor\File\FileList $fileList
    ) {
        $this->relatedFile = $relatedFile;
        $this->errorHandler = $errorHandler;
        $this->fileList = $fileList;
    }

    /**
     * Explode import paths
     *
     * @param \Magento\Less\PreProcessor\File\Less $lessFile
     * @param array $matchedPaths
     * @return array
     */
    protected function generatePaths(PreProcessor\File\Less $lessFile, $matchedPaths)
    {
        $importPaths = array();
        foreach ($matchedPaths as $path) {
            try {
                $viewParams = $lessFile->getViewParams();
                $resolvedPath = $this->relatedFile->buildPath(
                    $this->preparePath($path),
                    $lessFile->getFilePath(),
                    $viewParams
                );
                $importedLessFile = $this->fileList->createFile($resolvedPath, $viewParams);
                $this->fileList->addFile($importedLessFile);
                $importPaths[$path] = $importedLessFile->getPublicationPath();
            } catch (\Magento\Filesystem\FilesystemException $e) {
                $this->errorHandler->processException($e);
            }
        }
        return $importPaths;
    }

    /**
     * Prepare relative path to less compatible state
     *
     * @param string $lessSourcePath
     * @return string
     */
    protected function preparePath($lessSourcePath)
    {
        return pathinfo($lessSourcePath, PATHINFO_EXTENSION) ? $lessSourcePath : $lessSourcePath . '.less';
    }

    /**
     * {@inheritdoc}
     */
    public function process(PreProcessor\File\Less $lessFile, $lessContent)
    {
        $matches = [];
        preg_match_all(self::REPLACE_PATTERN, $lessContent, $matches);
        $importPaths = $this->generatePaths($lessFile, $matches['path']);
        $replaceCallback = function ($matchContent) use ($importPaths) {
            return $this->replace($matchContent, $importPaths);
        };
        return preg_replace_callback(self::REPLACE_PATTERN, $replaceCallback, $lessContent);
    }

    /**
     * Replace import path to file
     *
     * @param array $matchContent
     * @param array $importPaths
     * @return string
     */
    protected function replace($matchContent, $importPaths)
    {
        if (empty($importPaths[$matchContent['path']])) {
            return '';
        }
        $filePath = $importPaths[$matchContent['path']];
        $typeString = empty($matchContent['type']) ? '' : '(' . $matchContent['type'] . ') ';
        $mediaString = empty($matchContent['media']) ? '' : ' ' . $matchContent['media'];
        return "@import {$typeString}'{$filePath}'{$mediaString};";
    }
}
