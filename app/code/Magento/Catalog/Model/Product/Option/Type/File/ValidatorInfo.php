<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Option\Type\File;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Size;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\MediaStorage\Model\File\Validator\NotProtectedExtension;

/**
 * Validator for existing files.
 */
class ValidatorInfo extends Validator
{
    /**
     * @var Database
     */
    protected $coreFileStorageDatabase;

    /**
     * @var ValidateFactory
     */
    protected $validateFactory;

    /**
     * @var mixed
     */
    protected $useQuotePath;

    /**
     * @var string
     */
    protected $fileFullPath;

    /**
     * @var string
     */
    protected $fileRelativePath;

    /**
     * @var IoFile
     */
    private $ioFile;

    /**
     * @var NotProtectedExtension
     */
    private $fileValidator;

    /**
     * Construct method
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Filesystem $filesystem
     * @param Size $fileSize
     * @param Database $coreFileStorageDatabase
     * @param ValidateFactory $validateFactory
     * @param NotProtectedExtension $fileValidator
     * @param IoFile $ioFile
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Filesystem $filesystem,
        Size $fileSize,
        Database $coreFileStorageDatabase,
        ValidateFactory $validateFactory,
        NotProtectedExtension $fileValidator,
        IoFile $ioFile
    ) {
        $this->coreFileStorageDatabase = $coreFileStorageDatabase;
        $this->validateFactory = $validateFactory;
        $this->fileValidator = $fileValidator;
        $this->ioFile = $ioFile;
        parent::__construct($scopeConfig, $filesystem, $fileSize);
    }

    /**
     * Setter method for property "useQuotePath"
     *
     * @param mixed $useQuotePath
     * @return $this
     */
    public function setUseQuotePath($useQuotePath)
    {
        $this->useQuotePath = $useQuotePath;
        return $this;
    }

    /**
     * Validate method for the option value depends on an option
     *
     * @param array $optionValue
     * @param \Magento\Catalog\Model\Product\Option $option
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate($optionValue, $option)
    {
        if (!is_array($optionValue)) {
            return false;
        }

        $this->fileFullPath = null;
        $this->fileRelativePath = null;
        $this->initFilePath($optionValue);

        if ($this->fileFullPath === null) {
            return false;
        }

        $validatorChain = $this->validateFactory->create();
        try {
            $validatorChain = $this->buildImageValidator($validatorChain, $option, $this->fileFullPath);
        } catch (InputException $notImage) {
            return false;
        }

        if ($this->validatePath($optionValue) && $validatorChain->isValid($this->fileFullPath, $optionValue['title'])) {
            return $this->rootDirectory->isReadable($this->fileRelativePath)
                && isset($optionValue['secret_key'])
                && $this->buildSecretKey($this->fileRelativePath) == $optionValue['secret_key'];
        } else {
            $errors = $this->getValidatorErrors($validatorChain->getErrors(), $optionValue, $option);
            if (count($errors) > 0) {
                throw new LocalizedException(__(implode("\n", $errors)));
            }
            throw new LocalizedException(
                __("The product's required option(s) weren't entered. Make sure the options are entered and try again.")
            );
        }
    }

    /**
     * Validate quote_path and order_path.
     *
     * @param array $optionValuePath
     * @return bool
     */
    private function validatePath(array $optionValuePath): bool
    {
        foreach ([$optionValuePath['quote_path'], $optionValuePath['order_path']] as $path) {
            $pathInfo = $this->ioFile->getPathInfo($path);

            if (isset($pathInfo['extension'])
                && (empty($pathInfo['extension']) || !$this->fileValidator->isValid($pathInfo['extension']))
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Method for creation secret key for the given file
     *
     * @param string $fileRelativePath
     * @return string
     */
    protected function buildSecretKey($fileRelativePath)
    {
        return substr(hash('sha256', $this->rootDirectory->readFile($fileRelativePath)), 0, 20);
    }

    /**
     * Calculates path for the file
     *
     * @param array $optionValue
     * @return void
     */
    protected function initFilePath($optionValue)
    {
        /**
         * @see \Magento\Catalog\Model\Product\Option\Type\File\ValidatorFile::validate
         *              There setUserValue() sets correct fileFullPath only for
         *              quote_path. So we must form both full paths manually and
         *              check them.
         */
        $checkPaths = [];
        if (isset($optionValue['quote_path'])) {
            $checkPaths[] = $optionValue['quote_path'];
        }
        if (isset($optionValue['order_path']) && !$this->useQuotePath) {
            $checkPaths[] = $optionValue['order_path'];
        }

        foreach ($checkPaths as $path) {
            if (!$this->rootDirectory->isFile($path)) {
                if (!$this->coreFileStorageDatabase->saveFileToFilesystem($path)) {
                    continue;
                }
            }
            $this->fileFullPath = $this->rootDirectory->getAbsolutePath($path);
            $this->fileRelativePath = $path;
            break;
        }
    }
}
