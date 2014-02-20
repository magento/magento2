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
     * @var \Magento\Less\PreProcessor
     */
    protected $preProcessor;

    /**
     * @var \Magento\View\RelatedFile
     */
    protected $relatedFile;

    /**
     * @var PreProcessor\ErrorHandlerInterface
     */
    protected $errorHandler;

    /**
     * @param PreProcessor $preProcessor
     * @param PreProcessor\ErrorHandlerInterface $errorHandler
     * @param \Magento\View\RelatedFile $relatedFile
     */
    public function __construct(
        PreProcessor $preProcessor,
        PreProcessor\ErrorHandlerInterface $errorHandler,
        \Magento\View\RelatedFile $relatedFile
    ) {
        $this->preProcessor = $preProcessor;
        $this->errorHandler = $errorHandler;
        $this->relatedFile = $relatedFile;
    }

    /**
     * Explode import paths
     *
     * @param array $matchedPaths
     * @param array $viewParams
     * @param array $params
     * @return array
     */
    protected function generatePaths($matchedPaths, $viewParams, array $params)
    {
        $importPaths = array();
        foreach ($matchedPaths as $path) {
            $resolvedPath = $this->relatedFile->buildPath(
                $this->preparePath($path),
                $params['parentAbsolutePath'],
                $params['parentPath'],
                $viewParams
            );
            try {
                $importPaths[$path] = $this->preProcessor->processLessInstructions(
                    $resolvedPath,
                    $viewParams
                );
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
    public function process($lessContent, array $viewParams, array $params = [])
    {
        $matches = [];
        preg_match_all(self::REPLACE_PATTERN, $lessContent, $matches);
        $importPaths = $this->generatePaths($matches['path'], $viewParams, $params);
        $replaceCallback = function ($matchContent) use ($importPaths) {
            return $this->replace($matchContent, $importPaths);
        };
        return preg_replace_callback(self::REPLACE_PATTERN, $replaceCallback, $lessContent);
    }

    /**
     * Replace import path to file
     *
     * @param array $matchContent
     * @param $importPaths
     * @return string
     */
    protected function replace($matchContent, $importPaths)
    {
        $path = $matchContent['path'];
        if (empty($importPaths[$path])) {
            return '';
        }
        $typeString  = empty($matchContent['type']) ? '' : '(' . $matchContent['type'] . ') ';
        $mediaString  = empty($matchContent['media']) ? '' : ' ' . $matchContent['media'];
        return "@import {$typeString}'{$importPaths[$path]}'{$mediaString};";
    }
}
