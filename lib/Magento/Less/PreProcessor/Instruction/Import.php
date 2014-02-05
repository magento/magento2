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
     * Import's path list where key is relative path and value is absolute path to the imported content
     *
     * @var array
     */
    protected $importPaths = [];

    /**
     * @var \Magento\Less\PreProcessor
     */
    protected $preProcessor;

    /**
     * @var \Magento\Logger
     */
    protected $logger;

    /**
     * @var array
     */
    protected $viewParams;

    /**
     * @param \Magento\Less\PreProcessor $preProcessor
     * @param \Magento\Logger $logger
     * @param array $viewParams
     */
    public function __construct(
        \Magento\Less\PreProcessor $preProcessor,
        \Magento\Logger $logger,
        array $viewParams = array()
    ) {
        $this->preProcessor = $preProcessor;
        $this->logger = $logger;
        $this->viewParams = $viewParams;
    }

    /**
     * Explode import paths
     *
     * @param array $importPaths
     * @return $this
     */
    protected function generatePaths($importPaths)
    {
        foreach ($importPaths as $path) {
            $path = $this->preparePath($path);
            try {
                $this->importPaths[$path] = $this->preProcessor->processLessInstructions($path, $this->viewParams);
            } catch (\Magento\Filesystem\FilesystemException $e) {
                $this->logger->logException($e);
            }
        }
        return $this;
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
    public function process($lessContent)
    {
        $matches = [];
        preg_match_all(self::REPLACE_PATTERN, $lessContent, $matches);
        $this->generatePaths($matches['path']);
        return preg_replace_callback(self::REPLACE_PATTERN, array($this, 'replace'), $lessContent);
    }

    /**
     * Replace import path to file
     *
     * @param array $matchContent
     * @return string
     */
    protected function replace($matchContent)
    {
        $path = $this->preparePath($matchContent['path']);
        if (empty($this->importPaths[$path])) {
            return '';
        }
        $typeString  = empty($matchContent['type']) ? '' : '(' . $matchContent['type'] . ') ';
        return "@import {$typeString}'{$this->importPaths[$path]}';";
    }
}
