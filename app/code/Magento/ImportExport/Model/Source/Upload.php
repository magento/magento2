<?php

namespace Magento\ImportExport\Model\Source;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Adapter\FileTransferFactory;
use Magento\Framework\Math\Random;
use Magento\ImportExport\Helper\Data as DataHelper;
use Magento\ImportExport\Model\Import;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Upload
{
    /**
     * @var FileTransferFactory
     */
    protected $_httpFactory;

    /**
     * @var DataHelper
     */
    protected $_importExportData = null;

    /**
     * @var UploaderFactory
     */
    protected $_uploaderFactory;

    /**
     * @var Random
     */
    private $random;

    /**
     * @param FileTransferFactory $httpFactory
     * @param DataHelper $importExportData
     * @param UploaderFactory $uploaderFactory
     * @param Random|null $random
     */
    public function __construct(
        FileTransferFactory $httpFactory,
        DataHelper $importExportData,
        UploaderFactory $uploaderFactory,
        Random $random
    ) {
        $this->_httpFactory = $httpFactory;
        $this->_importExportData = $importExportData;
        $this->_uploaderFactory = $uploaderFactory;
        $this->random = $random ?: ObjectManager::getInstance()
            ->get(Random::class);
    }
    /**
     * Move uploaded file.
     *
     * @param Import
     * @throws LocalizedException
     * @return string Source file path
     */
    public function uploadSource(Import $import)
    {
        /** @var $adapter \Zend_File_Transfer_Adapter_Http */
        $adapter = $this->_httpFactory->create();
        if (!$adapter->isValid(Import::FIELD_NAME_SOURCE_FILE)) {
            $errors = $adapter->getErrors();
            if ($errors[0] == \Zend_Validate_File_Upload::INI_SIZE) {
                $errorMessage = $this->_importExportData->getMaxUploadSizeMessage();
            } else {
                $errorMessage = __('The file was not uploaded.');
            }
            throw new LocalizedException($errorMessage);
        }

        $entity = $import->getEntity();
        /** @var $uploader Uploader */
        $uploader = $this->_uploaderFactory->create(['fileId' => Import::FIELD_NAME_SOURCE_FILE]);
        $uploader->setAllowedExtensions(['csv', 'zip']);
        $uploader->skipDbProcessing(true);
        $fileName = $this->random->getRandomString(32) . '.' . $uploader->getFileExtension();
        try {
            $result = $uploader->save($import->getWorkingDir(), $fileName);
        } catch (\Exception $e) {
            throw new LocalizedException(__('The file cannot be uploaded.'));
        }

        $extension = '';
        $uploadedFile = '';
        if ($result !== false) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $extension = pathinfo($result['file'], PATHINFO_EXTENSION);
            $uploadedFile = $result['path'] . $result['file'];
        }

        if (!$extension) {
            $import->getVarDirectory()->delete($uploadedFile);
            throw new LocalizedException(__('The file you uploaded has no extension.'));
        }
        $sourceFile = $import->getWorkingDir() . $entity;

        $sourceFile .= '.' . $extension;
        $sourceFileRelative = $import->getVarDirectory()->getRelativePath($sourceFile);

        if (strtolower($uploadedFile) != strtolower($sourceFile)) {
            if ($import->getVarDirectory()->isExist($sourceFileRelative)) {
                $import->getVarDirectory()->delete($sourceFileRelative);
            }

            try {
                $import->getVarDirectory()->renameFile(
                    $import->getVarDirectory()->getRelativePath($uploadedFile),
                    $sourceFileRelative
                );
            } catch (FileSystemException $e) {
                throw new LocalizedException(__('The source file moving process failed.'));
            }
        }
        $import->_removeBom($sourceFile);
        $import->createHistoryReport($sourceFileRelative, $entity, $extension, $result);
        return $sourceFile;
    }

    /**
     * Move uploaded file and provide source instance.
     *
     * @return Import\AbstractSource
     * @throws LocalizedException
     * @since 100.2.7
     */
    public function uploadFileAndGetSourceForRest()
    {
        $entity = $this->getEntity();
        /** @var $uploader Uploader */
        $fileName = $this->random->getRandomString(32) . '.' . 'csv';
        $uploadedFile = '';
        $extension = 'csv';
        $uploadedFile = $this->getWorkingDir() . $fileName;

        if (!$extension) {
            $this->_varDirectory->delete($uploadedFile);
            throw new LocalizedException(__('The file you uploaded has no extension.'));
        }
        $sourceFile = $this->getWorkingDir() . $entity;

        $sourceFile .= '.' . $extension;
        $sourceFileRelative = $this->_varDirectory->getRelativePath($sourceFile);
        $this->filesystemIo->cp($sourceFile, $uploadedFile);

        if (strtolower($uploadedFile) != strtolower($sourceFile)) {
            if ($this->_varDirectory->isExist($sourceFileRelative)) {
                $this->_varDirectory->delete($sourceFileRelative);
            }

            try {
                $this->_varDirectory->renameFile(
                    $this->_varDirectory->getRelativePath($uploadedFile),
                    $sourceFileRelative
                );
            } catch (FileSystemException $e) {
                throw new LocalizedException(__('The source file moving process failed.'));
            }
        }
        $this->_removeBom($sourceFile);
        $this->createHistoryReport($sourceFileRelative, $entity, $extension, ['name'=> $entity . 'csv']);
        try {
            $source = $this->_getSourceAdapter($sourceFile);
        } catch (\Exception $e) {
            $this->_varDirectory->delete($this->_varDirectory->getRelativePath($sourceFile));
            throw new LocalizedException(__($e->getMessage()));
        }

        return $source;
    }
}
