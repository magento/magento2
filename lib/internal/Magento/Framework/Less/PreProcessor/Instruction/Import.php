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

namespace Magento\Framework\Less\PreProcessor\Instruction;

use Magento\Framework\View\Asset\PreProcessorInterface;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\ModuleNotation;

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
     * @var \Magento\Framework\View\Asset\ModuleNotation\Resolver
     */
    private $notationResolver;

    /**
     * @var array
     */
    protected $relatedFiles = array();

    /**
     * @param ModuleNotation\Resolver $notationResolver
     */
    public function __construct(ModuleNotation\Resolver $notationResolver)
    {
        $this->notationResolver = $notationResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function process(\Magento\Framework\View\Asset\PreProcessor\Chain $chain)
    {
        $asset = $chain->getAsset();
        $replaceCallback = function ($matchContent) use ($asset) {
            return $this->replace($matchContent, $asset);
        };
        $chain->setContent(preg_replace_callback(self::REPLACE_PATTERN, $replaceCallback, $chain->getContent()));
    }

    /**
     * Retrieve information on all related files, processed so far
     *
     * BUG: this information about related files is not supposed to be in the state of this object.
     * This class is meant to be a service (shareable instance) without such a transient state.
     * The list of related files needs to be accumulated for the LESS preprocessor (\Magento\Framework\Css\PreProcessor\Less),
     * because it uses a 3rd-party library, which requires the files to physically reside in the base same directory.
     *
     * @return array
     */
    public function getRelatedFiles()
    {
        return $this->relatedFiles;
    }

    /**
     * Clear the record of related files, processed so far
     * @return void
     */
    public function resetRelatedFiles()
    {
        $this->relatedFiles = array();
    }

    /**
     * Add related file to the record of processed files
     *
     * @param string $matchedFileId
     * @param LocalInterface $asset
     * @return void
     */
    protected function recordRelatedFile($matchedFileId, LocalInterface $asset)
    {
        $this->relatedFiles[] = array($matchedFileId, $asset);
    }

    /**
     * Return replacement of an original @import directive
     *
     * @param array $matchedContent
     * @param LocalInterface $asset
     * @return string
     */
    protected function replace(array $matchedContent, LocalInterface $asset)
    {
        $matchedFileId = $this->fixFileExtension($matchedContent['path']);
        $this->recordRelatedFile($matchedFileId, $asset);
        $resolvedPath = $this->notationResolver->convertModuleNotationToPath($asset, $matchedFileId);
        $typeString = empty($matchedContent['type']) ? '' : '(' . $matchedContent['type'] . ') ';
        $mediaString = empty($matchedContent['media']) ? '' : ' ' . trim($matchedContent['media']);
        return "@import {$typeString}'{$resolvedPath}'{$mediaString};";
    }

    /**
     * Resolve extension of imported asset according to the specification of LESS format
     *
     * @param string $fileId
     * @return string
     * @link http://lesscss.org/features/#import-directives-feature-file-extensions
     */
    protected function fixFileExtension($fileId)
    {
        if (!pathinfo($fileId, PATHINFO_EXTENSION)) {
            $fileId .= '.less';
        }
        return $fileId;
    }
}
