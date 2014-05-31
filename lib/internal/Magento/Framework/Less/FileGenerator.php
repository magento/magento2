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

namespace Magento\Framework\Less;

use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\Source;

class FileGenerator
{
    /**
     * Temporary directory prefix
     */
    const TMP_LESS_DIR = 'less';

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $tmpDirectory;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepo;

    /**
     * @var \Magento\Framework\Less\PreProcessor\Instruction\MagentoImport
     */
    private $magentoImportProcessor;

    /**
     * @var \Magento\Framework\Less\PreProcessor\Instruction\Import
     */
    private $importProcessor;

    /**
     * @param \Magento\Framework\App\Filesystem $filesystem
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\Less\PreProcessor\Instruction\MagentoImport $magentoImportProcessor
     * @param \Magento\Framework\Less\PreProcessor\Instruction\Import $importProcessor
     */
    public function __construct(
        \Magento\Framework\App\Filesystem $filesystem,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Less\PreProcessor\Instruction\MagentoImport $magentoImportProcessor,
        \Magento\Framework\Less\PreProcessor\Instruction\Import $importProcessor
    ) {
        $this->tmpDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem::VAR_DIR);
        $this->assetRepo = $assetRepo;
        $this->magentoImportProcessor = $magentoImportProcessor;
        $this->importProcessor = $importProcessor;
    }

    /**
     * Create a tree of self-sustainable files and return the topmost LESS file, ready for passing to 3rd party library
     *
     * @param \Magento\Framework\View\Asset\PreProcessor\Chain $chain
     * @return string Absolute path of generated LESS file
     */
    public function generateLessFileTree(\Magento\Framework\View\Asset\PreProcessor\Chain $chain)
    {
        /**
         * @bug This logic is duplicated at \Magento\Framework\View\Asset\PreProcessor\Pool::getPreProcessors()
         * If you need to extend or modify behavior of LESS preprocessing, you must account for both places
         */
        $this->magentoImportProcessor->process($chain);
        $this->importProcessor->process($chain);
        $this->generateRelatedFiles();
        $lessRelativePath = preg_replace('#\.css$#', '.less', $chain->getAsset()->getPath());
        $tmpFilePath = $this->createFile($lessRelativePath, $chain->getContent());
        return $tmpFilePath;
    }

    /**
     * Create all asset files, referenced from already processed ones
     *
     * @return void
     */
    protected function generateRelatedFiles()
    {
        do {
            $relatedFiles = $this->importProcessor->getRelatedFiles();
            $this->importProcessor->resetRelatedFiles();
            foreach ($relatedFiles as $relatedFileInfo) {
                list($relatedFileId, $asset) = $relatedFileInfo;
                $this->generateRelatedFile($relatedFileId, $asset);
            }
        } while ($relatedFiles);
    }

    /**
     * Create file, referenced relatively to an asset
     *
     * @param string $relatedFileId
     * @param LocalInterface $asset
     * @return void
     */
    protected function generateRelatedFile($relatedFileId, LocalInterface $asset)
    {
        $relatedAsset = $this->assetRepo->createRelated($relatedFileId, $asset);
        $this->createFile($relatedAsset->getPath(), $relatedAsset->getContent());
    }

    /**
     * Write down contents to a temporary file and return its absolute path
     *
     * @param string $relativePath
     * @param string $contents
     * @return string
     */
    protected function createFile($relativePath, $contents)
    {
        $filePath = Source::TMP_MATERIALIZATION_DIR . '/' . self::TMP_LESS_DIR . '/' . $relativePath;
        $this->tmpDirectory->writeFile($filePath, $contents);
        return $this->tmpDirectory->getAbsolutePath($filePath);
    }
}
