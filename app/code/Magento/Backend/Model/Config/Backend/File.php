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
 * @category    Magento
 * @package     Magento_Backend
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Backend\Model\Config\Backend;

/**
 * System config file field backend model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class File extends \Magento\Core\Model\Config\Value
{
    /**
     * @var \Magento\Backend\Model\Config\Backend\File\RequestData\RequestDataInterface
     */
    protected $_requestData;

    /**
     * Upload max file size in kilobytes
     *
     * @var int
     */
    protected $_maxFileSize = 0;

    /**
     * @var \Magento\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Core\Model\File\UploaderFactory
     */
    protected $_uploaderFactory;

    /**
     * @var \Magento\App\Dir
     */
    protected $_dir;

    /**
     * @param \Magento\Core\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Core\Model\StoreManager $storeManager
     * @param \Magento\Core\Model\Config $config
     * @param \Magento\Backend\Model\Config\Backend\File\RequestData\RequestDataInterface $requestData
     * @param \Magento\Filesystem $filesystem
     * @param \Magento\App\Dir $dir
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Core\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Core\Model\StoreManager $storeManager,
        \Magento\Core\Model\Config $config,
        \Magento\Backend\Model\Config\Backend\File\RequestData\RequestDataInterface $requestData,
        \Magento\Filesystem $filesystem,
        \Magento\App\Dir $dir,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_uploaderFactory = $uploaderFactory;
        $this->_requestData = $requestData;
        $this->_filesystem = $filesystem;
        $this->_dir = $dir;
        parent::__construct($context, $registry, $storeManager, $config, $resource, $resourceCollection, $data);
    }

    /**
     * Save uploaded file before saving config value
     *
     * @return \Magento\Backend\Model\Config\Backend\File
     * @throws \Magento\Core\Exception
     */
    protected function _beforeSave()
    {
        $value = $this->getValue();
        $tmpName = $this->_requestData->getTmpName($this->getPath());
        $file = array();
        if ($tmpName) {
            $file['tmp_name'] = $tmpName;
            $file['name'] = $this->_requestData->getName($this->getPath());
        } elseif (!empty($value['tmp_name'])) {
            $file['tmp_name'] = $value['tmp_name'];
            $file['name'] = $value['value'];
        }
        if (!empty($file)) {
            $uploadDir = $this->_getUploadDir();
            try {
                $uploader = $this->_uploaderFactory->create(array('fileId' => $file));
                $uploader->setAllowedExtensions($this->_getAllowedExtensions());
                $uploader->setAllowRenameFiles(true);
                $uploader->addValidateCallback('size', $this, 'validateMaxSize');
                $result = $uploader->save($uploadDir);
            } catch (\Exception $e) {
                throw new \Magento\Core\Exception($e->getMessage());
            }

            $filename = $result['file'];
            if ($filename) {
                if ($this->_addWhetherScopeInfo()) {
                    $filename = $this->_prependScopeInfo($filename);
                }
                $this->setValue($filename);
            }
        } else {
            if (is_array($value) && !empty($value['delete'])) {
                $this->setValue('');
            } else {
                $this->unsValue();
            }
        }

        return $this;
    }

    /**
     * Validation callback for checking max file size
     *
     * @param  string $filePath Path to temporary uploaded file
     * @throws \Magento\Core\Exception
     */
    public function validateMaxSize($filePath)
    {
        if ($this->_maxFileSize > 0
            && $this->_filesystem->getFileSize($filePath, dirname($filePath)) > ($this->_maxFileSize * 1024)) {
            throw new \Magento\Core\Exception(
                __('The file you\'re uploading exceeds the server size limit of %1 kilobytes.', $this->_maxFileSize)
            );
        }
    }

    /**
     * Makes a decision about whether to add info about the scope.
     *
     * @return boolean
     */
    protected function _addWhetherScopeInfo()
    {
        $fieldConfig = $this->getFieldConfig();
        $dirParams = array_key_exists('upload_dir', $fieldConfig) ? $fieldConfig['upload_dir'] : array();
        return (is_array($dirParams) && array_key_exists('scope_info', $dirParams) && $dirParams['scope_info']);
    }

    /**
     * Return path to directory for upload file
     *
     * @return string
     * @throws \Magento\Core\Exception
     */
    protected function _getUploadDir()
    {
        $fieldConfig = $this->getFieldConfig();
        /* @var $fieldConfig \Magento\Simplexml\Element */

        if (!array_key_exists('upload_dir', $fieldConfig)) {
            throw new \Magento\Core\Exception(
                __('The base directory to upload file is not specified.')
            );
        }

        if (is_array($fieldConfig['upload_dir'])) {
            $uploadDir = $fieldConfig['upload_dir']['value'];
            if (array_key_exists('scope_info', $fieldConfig['upload_dir'])
                && $fieldConfig['upload_dir']['scope_info']
            ) {
                $uploadDir = $this->_appendScopeInfo($uploadDir);
            }

            if (array_key_exists('config', $fieldConfig['upload_dir'])) {
                $uploadRoot = $this->_getUploadRoot($fieldConfig['upload_dir']['config']);
                $uploadDir = $uploadRoot . '/' . $uploadDir;
            }
        } else {
            $uploadDir = (string) $fieldConfig['upload_dir'];
        }

        return $uploadDir;
    }

    /**
     * Return the root part of directory path for uploading
     *
     * @var string
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getUploadRoot($token)
    {
        return $this->_dir->getDir(\Magento\App\Dir::MEDIA);
    }

    /**
     * Prepend path with scope info
     *
     * E.g. 'stores/2/path' , 'websites/3/path', 'default/path'
     *
     * @param string $path
     * @return string
     */
    protected function _prependScopeInfo($path)
    {
        $scopeInfo = $this->getScope();
        if ('default' != $this->getScope()) {
            $scopeInfo .= '/' . $this->getScopeId();
        }
        return $scopeInfo . '/' . $path;
    }

    /**
     * Add scope info to path
     *
     * E.g. 'path/stores/2' , 'path/websites/3', 'path/default'
     *
     * @param string $path
     * @return string
     */
    protected function _appendScopeInfo($path)
    {
        $path .= '/' . $this->getScope();
        if ('default' != $this->getScope()) {
            $path .= '/' . $this->getScopeId();
        }
        return $path;
    }

    /**
     * Getter for allowed extensions of uploaded files
     *
     * @return array
     */
    protected function _getAllowedExtensions()
    {
        return array();
    }
}
