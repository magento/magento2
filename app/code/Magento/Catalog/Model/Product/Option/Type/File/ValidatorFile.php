<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Option\Type\File;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Filesystem\DirectoryList;

class ValidatorFile extends Validator
{
    /**
     * Relative path for main destination folder
     *
     * @var string
     */
    protected $path = '/custom_options';

    /**
     * Relative path for quote folder
     *
     * @var string
     */
    protected $quotePath = '/custom_options/quote';

    /**
     * Relative path for order folder
     *
     * @var string
     */
    protected $orderPath = '/custom_options/order';

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\HTTP\Adapter\FileTransferFactory
     */
    protected $httpFactory;

    /**
     * @var Product
     */
    protected $product;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\File\Size $fileSize
     * @param \Magento\Framework\HTTP\Adapter\FileTransferFactory $httpFactory
     * @throws \Magento\Framework\Filesystem\FilesystemException
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\File\Size $fileSize,
        \Magento\Framework\HTTP\Adapter\FileTransferFactory $httpFactory
    ) {
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->filesystem = $filesystem;
        $this->httpFactory = $httpFactory;
        parent::__construct($scopeConfig, $filesystem, $fileSize);
    }

    /**
     * @param Product $product
     * @return $this
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;
        return $this;
    }

    /**
     * @param \Magento\Framework\Object $processingParams
     * @param \Magento\Catalog\Model\Product\Option $option
     * @return array
     * @throws \Magento\Framework\Model\Exception
     * @throws \Zend_File_Transfer_Exception
     */
    public function validate($processingParams, $option)
    {
        $upload = $this->httpFactory->create();
        $file = $processingParams->getFilesPrefix() . 'options_' . $option->getId() . '_file';
        try {
            $runValidation = $option->getIsRequire() || $upload->isUploaded($file);
            if (!$runValidation) {
                throw new RunValidationException();
            }

            $fileInfo = $upload->getFileInfo($file)[$file];
            $fileInfo['title'] = $fileInfo['name'];
        } catch (RunValidationException $r) {
            throw $r;
        } catch (\Exception $e) {
            // when file exceeds the upload_max_filesize, $_FILES is empty
            if ($this->validateContentLength()) {
                $value = $this->fileSize->getMaxFileSizeInMb();
                throw new LargeSizeException(
                    __("The file you uploaded is larger than %1 Megabytes allowed by server", $value)
                );
            } else {
                throw new OptionRequiredException();
            }
        }

        /**
         * Option Validations
         */
        $upload = $this->buildImageValidator($upload, $option);

        /**
         * Upload process
         */
        $this->initFilesystem();
        $userValue = [];

        if ($upload->isUploaded($file) && $upload->isValid($file)) {
            $extension = pathinfo(strtolower($fileInfo['name']), PATHINFO_EXTENSION);

            $fileName = \Magento\Core\Model\File\Uploader::getCorrectFileName($fileInfo['name']);
            $dispersion = \Magento\Core\Model\File\Uploader::getDispretionPath($fileName);

            $filePath = $dispersion;

            $tmpDirectory = $this->filesystem->getDirectoryRead(DirectoryList::SYS_TMP);
            $fileHash = md5($tmpDirectory->readFile($tmpDirectory->getRelativePath($fileInfo['tmp_name'])));
            $filePath .= '/' . $fileHash . '.' . $extension;
            $fileFullPath = $this->mediaDirectory->getAbsolutePath($this->quotePath . $filePath);

            $upload->addFilter(new \Zend_Filter_File_Rename(['target' => $fileFullPath, 'overwrite' => true]));

            // TODO: I don't know how change this
            if (!is_null($this->product)) {
                $this->product->getTypeInstance()->addFileQueue(
                    [
                        'operation' => 'receive_uploaded_file',
                        'src_name' => $file,
                        'dst_name' => $fileFullPath,
                        'uploader' => $upload,
                        'option' => $this,
                    ]
                );
            }

            $_width = 0;
            $_height = 0;

            if ($tmpDirectory->isReadable($tmpDirectory->getRelativePath($fileInfo['tmp_name']))) {
                $imageSize = getimagesize($fileInfo['tmp_name']);
                if ($imageSize) {
                    $_width = $imageSize[0];
                    $_height = $imageSize[1];
                }
            }
            $uri = $this->filesystem->getUri(DirectoryList::MEDIA);
            $userValue = [
                'type' => $fileInfo['type'],
                'title' => $fileInfo['name'],
                'quote_path' => $uri . $this->quotePath . $filePath,
                'order_path' => $uri . $this->orderPath . $filePath,
                'fullpath' => $fileFullPath,
                'size' => $fileInfo['size'],
                'width' => $_width,
                'height' => $_height,
                'secret_key' => substr($fileHash, 0, 20),
            ];
        } elseif ($upload->getErrors()) {
            $errors = $this->getValidatorErrors($upload->getErrors(), $fileInfo, $option);

            if (count($errors) > 0) {
                throw new Exception(implode("\n", $errors));
            }
        } else {
            throw new Exception(__('Please specify the product\'s required option(s).'));
        }
        return $userValue;
    }

    /**
     * Directory structure initializing
     *
     * @return void
     * @see \Magento\Catalog\Model\Product\Option\Type\File::_initFilesystem
     */
    protected function initFilesystem()
    {
        $this->mediaDirectory->create($this->path);
        $this->mediaDirectory->create($this->quotePath);
        $this->mediaDirectory->create($this->orderPath);

        // Directory listing and hotlink secure
        $path = $this->path . '/.htaccess';
        if (!$this->mediaDirectory->isFile($path)) {
            $this->mediaDirectory->writeFile($path, "Order deny,allow\nDeny from all");
        }
    }

    /**
     * @return bool
     * @todo need correctly name
     */
    protected function validateContentLength()
    {
        return isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > $this->fileSize->getMaxFileSize();
    }
}
