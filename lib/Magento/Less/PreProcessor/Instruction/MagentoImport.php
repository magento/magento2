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
     * @var \Magento\View\Layout\File\SourceInterface
     */
    protected $fileSource;

    /**
     * @var PreProcessor\ErrorHandlerInterface
     */
    protected $errorHandler;

    /**
     * @var \Magento\View\RelatedFile
     */
    protected $relatedFile;

    /**
     * @var \Magento\View\Service
     */
    protected $viewService;

    /**
     * @param \Magento\View\Layout\File\SourceInterface $fileSource
     * @param \Magento\View\Service $viewService
     * @param \Magento\View\RelatedFile $relatedFile
     * @param PreProcessor\ErrorHandlerInterface $errorHandler
     */
    public function __construct(
        \Magento\View\Layout\File\SourceInterface $fileSource,
        \Magento\View\Service $viewService,
        \Magento\View\RelatedFile $relatedFile,
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
    public function process($lessContent, array $viewParams, array $paths = [])
    {
        $this->viewService->updateDesignParams($viewParams);
        $replaceCallback = function ($matchContent) use ($viewParams, $paths) {
            return $this->replace($matchContent, $viewParams, $paths);
        };
        return preg_replace_callback(self::REPLACE_PATTERN, $replaceCallback, $lessContent);
    }

    /**
     * Replace @magento_import to @import less instructions
     *
     * @param array $matchContent
     * @param array $viewParams
     * @param array $paths
     * @return string
     */
    protected function replace($matchContent, $viewParams, $paths)
    {
        $importsContent = '';
        try {
            $resolvedPath = $this->relatedFile->buildPath(
                $matchContent['path'],
                $paths['parentAbsolutePath'],
                $paths['parentPath'],
                $viewParams
            );
            $importFiles = $this->fileSource->getFiles($viewParams['themeModel'], $resolvedPath);
            /** @var $importFile \Magento\View\Layout\File */
            foreach ($importFiles as $importFile) {
                $importsContent .= "@import '{$importFile->getFilename()}';\n";
            }
        } catch (\LogicException $e) {
            $this->errorHandler->processException($e);
        }
        return $importsContent;
    }
}
