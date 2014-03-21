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

use Magento\Less\PreProcessor;
use Magento\Less\PreProcessorInterface;
use Magento\View;

/**
 * Less @magento_import instruction preprocessor
 */
class MagentoImport implements PreProcessorInterface
{
    /**
     * Pattern of @import less instruction
     */
    const REPLACE_PATTERN = '#//@magento_import\s+[\'\"](?P<path>(?![/\\\]|\w:[/\\\])[^\"\']+)[\'\"]\s*?;#';

    /**
     * Layout file source
     *
     * @var \Magento\View\Layout\File\SourceInterface
     */
    protected $fileSource;

    /**
     * Pre-processor error handler
     *
     * @var PreProcessor\ErrorHandlerInterface
     */
    protected $errorHandler;

    /**
     * Related file
     *
     * @var \Magento\View\RelatedFile
     */
    protected $relatedFile;

    /**
     * View service
     *
     * @var \Magento\View\Service
     */
    protected $viewService;

    /**
     * @param View\Layout\File\SourceInterface $fileSource
     * @param View\Service $viewService
     * @param View\RelatedFile $relatedFile
     * @param PreProcessor\ErrorHandlerInterface $errorHandler
     */
    public function __construct(
        View\Layout\File\SourceInterface $fileSource,
        View\Service $viewService,
        View\RelatedFile $relatedFile,
        PreProcessor\ErrorHandlerInterface $errorHandler
    ) {
        $this->fileSource = $fileSource;
        $this->viewService = $viewService;
        $this->relatedFile = $relatedFile;
        $this->errorHandler = $errorHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function process(PreProcessor\File\Less $lessFile, $lessContent)
    {
        $viewParams = $lessFile->getViewParams();
        $parentPath = $lessFile->getFilePath();
        $this->viewService->updateDesignParams($viewParams);
        $replaceCallback = function ($matchContent) use ($viewParams, $parentPath) {
            return $this->replace($matchContent, $viewParams, $parentPath);
        };
        return preg_replace_callback(self::REPLACE_PATTERN, $replaceCallback, $lessContent);
    }

    /**
     * Replace @magento_import to @import less instructions
     *
     * @param array $matchContent
     * @param array $viewParams
     * @param string $parentPath
     * @return string
     */
    protected function replace($matchContent, $viewParams, $parentPath)
    {
        $importsContent = '';
        try {
            $resolvedPath = $this->relatedFile->buildPath($matchContent['path'], $parentPath, $viewParams);
            $importFiles = $this->fileSource->getFiles($viewParams['themeModel'], $resolvedPath);
            /** @var $importFile \Magento\View\Layout\File */
            foreach ($importFiles as $importFile) {
                $importsContent .= $importFile->getModule() ? "@import '{$importFile
                    ->getModule()}::{$resolvedPath}';\n" : "@import '{$matchContent['path']}';\n";
            }
        } catch (\LogicException $e) {
            $this->errorHandler->processException($e);
        }
        return $importsContent;
    }
}
