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

namespace Magento\Catalog\Model\Product\Option\Type\File;

class ValidatorInfo extends Validator
{
    /**
     * @var \Magento\Core\Helper\File\Storage\Database
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
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\File\Size $fileSize
     * @param \Magento\Core\Helper\File\Storage\Database $coreFileStorageDatabase
     * @param ValidateFactory $validateFactory
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\File\Size $fileSize,
        \Magento\Core\Helper\File\Storage\Database $coreFileStorageDatabase,
        \Magento\Catalog\Model\Product\Option\Type\File\ValidateFactory $validateFactory
    ) {
        $this->coreFileStorageDatabase = $coreFileStorageDatabase;
        $this->validateFactory = $validateFactory;
        parent::__construct($scopeConfig, $filesystem, $fileSize);
    }

    /**
     * @param mixed $useQuotePath
     * @return $this
     */
    public function setUseQuotePath($useQuotePath)
    {
        $this->useQuotePath = $useQuotePath;
        return $this;
    }

    /**
     * @param array $optionValue
     * @param \Magento\Catalog\Model\Product\Option $option
     * @return bool
     * @throws \Magento\Framework\Model\Exception
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
        } catch (NotImageException $notImage) {
            return false;
        }

        $result = false;
        if ($validatorChain->isValid($this->fileFullPath)) {
            $result = $this->rootDirectory->isReadable($this->fileRelativePath)
                && isset($optionValue['secret_key'])
                && $this->buildSecretKey($this->fileRelativePath) == $optionValue['secret_key'];

        } elseif ($validatorChain->getErrors()) {
            $errors = $this->getValidatorErrors($validatorChain->getErrors(), $optionValue, $option);

            if (count($errors) > 0) {
                throw new \Magento\Framework\Model\Exception(implode("\n", $errors));
            }
        } else {
            throw new \Magento\Framework\Model\Exception(__('Please specify the product\'s required option(s).'));
        }
        return $result;
    }

    /**
     * @param string $fileRelativePath
     * @return string
     */
    protected function buildSecretKey($fileRelativePath)
    {
        return substr(md5($this->rootDirectory->readFile($fileRelativePath)), 0, 20);
    }

    /**
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
        $checkPaths = array();
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
