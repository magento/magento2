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

use Magento\View\Asset\PreProcessor\PreProcessorInterface;

/**
 * Css pre-processor less
 */
class Less implements PreProcessorInterface
{
    /**
     * Temporary directory prefix
     */
    const TMP_LESS_DIR = 'less';

    /**
     * @var \Magento\Less\PreProcessor
     */
    protected $lessPreProcessor;

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var \Magento\Logger
     */
    protected $logger;

    /**
     * @var \Magento\View\Publisher\FileFactory
     */
    protected $fileFactory;

    /**
     * @param \Magento\Less\PreProcessor $lessPreProcessor
     * @param AdapterInterface $adapter
     * @param \Magento\Logger $logger
     * @param \Magento\View\Publisher\FileFactory $fileFactory
     */
    public function __construct(
        \Magento\Less\PreProcessor $lessPreProcessor,
        AdapterInterface $adapter,
        \Magento\Logger $logger,
        \Magento\View\Publisher\FileFactory $fileFactory
    ) {
        $this->lessPreProcessor = $lessPreProcessor;
        $this->adapter = $adapter;
        $this->logger = $logger;
        $this->fileFactory = $fileFactory;
    }

    /**
     * Process LESS file content
     *
     * @param \Magento\View\Publisher\FileInterface $publisherFile
     * @param \Magento\Filesystem\Directory\WriteInterface $targetDirectory
     * @return \Magento\View\Publisher\FileInterface
     */
    public function process(\Magento\View\Publisher\FileInterface $publisherFile, $targetDirectory)
    {
        // if css file has being already found_by_fallback or prepared_by_previous_pre-processor
        if ($publisherFile->getSourcePath()) {
            return $publisherFile;
        }

        try {
            $processedFiles = $this->lessPreProcessor->processLessInstructions(
                $this->replaceExtension($publisherFile->getFilePath(), 'css', 'less'),
                $publisherFile->getViewParams()
            );
            $cssContent = $this->adapter->process($processedFiles->getPublicationPath());
            $cssTrimmedContent = trim($cssContent);
            if (empty($cssTrimmedContent)) {
                return $publisherFile;
            }
        } catch (\Magento\Filesystem\FilesystemException $e) {
            $this->logger->logException($e);
            // It has 'null' source path
            return $publisherFile;
        } catch (Adapter\AdapterException $e) {
            $this->logger->logException($e);
            // It has 'null' source path
            return $publisherFile;
        } catch (\Less_Exception_Compiler $e) {
            $this->logger->logException($e);
            return $publisherFile;
        }

        $tmpFilePath = Composite::TMP_VIEW_DIR . '/' . self::TMP_LESS_DIR . '/' . $publisherFile->buildUniquePath();
        $targetDirectory->writeFile($tmpFilePath, $cssContent);

        $processedFile = $this->fileFactory->create(
            $publisherFile->getFilePath(),
            $publisherFile->getViewParams(),
            $targetDirectory->getAbsolutePath($tmpFilePath)
        );

        return $processedFile;
    }

    /**
     * @param string $filePath
     * @param string $search
     * @param string $replace
     * @return string
     */
    protected function replaceExtension($filePath, $search, $replace)
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        if ($extension === $search) {
            $dotPosition = strrpos($filePath, '.');
            $filePath = substr($filePath, 0, $dotPosition + 1) . $replace;
        }

        return $filePath;
    }
}
