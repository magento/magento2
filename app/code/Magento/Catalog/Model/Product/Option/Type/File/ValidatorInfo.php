<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Option\Type\File;

/**
 * Validator for existing files.
 */
class ValidatorInfo extends Validator
{
    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
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
     * Construct method
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\File\Size $fileSize
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDatabase
     * @param ValidateFactory $validateFactory
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\File\Size $fileSize,
        \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDatabase,
        \Magento\Catalog\Model\Product\Option\Type\File\ValidateFactory $validateFactory
    ) {
        $this->coreFileStorageDatabase = $coreFileStorageDatabase;
        $this->validateFactory = $validateFactory;
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
        } catch (\Magento\Framework\Exception\InputException $notImage) {
            return false;
        }

        $result = false;
        if ($validatorChain->isValid($this->fileFullPath, $optionValue['title'])) {
            $result = $this->rootDirectory->isReadable($this->fileRelativePath)
                && isset($optionValue['secret_key'])
                && $this->buildSecretKey($this->fileRelativePath) == $optionValue['secret_key'];
        } elseif ($validatorChain->getErrors()) {
            $errors = $this->getValidatorErrors($validatorChain->getErrors(), $optionValue, $option);

            if (count($errors) > 0) {
                throw new \Magento\Framework\Exception\LocalizedException(__(implode("\n", $errors)));
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __("The product's required option(s) weren't entered. Make sure the options are entered and try again.")
            );
        }
        return $result;
    }

    /**
     * Method for creation secret key for the given file
     *
     * @param string $fileRelativePath
     * @return string
     */
    protected function buildSecretKey($fileRelativePath)
    {
        return substr(md5($this->rootDirectory->readFile($fileRelativePath)), 0, 20);
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
