<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Controller\Adminhtml\Downloadable\File;

use Magento\Framework\Controller\ResultFactory;

class Upload extends \Magento\Downloadable\Controller\Adminhtml\Downloadable\File
{
    /**
     * @var \Magento\Downloadable\Model\Link
     */
    protected $_link;

    /**
     * @var \Magento\Downloadable\Model\Sample
     */
    protected $_sample;

    /**
     * Downloadable file helper.
     *
     * @var \Magento\Downloadable\Helper\File
     */
    protected $_fileHelper;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    private $storageDatabase;

    /**
     *
     * Copyright © Magento, Inc. All rights reserved.
     * See COPYING.txt for license details.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Downloadable\Model\Link $link
     * @param \Magento\Downloadable\Model\Sample $sample
     * @param \Magento\Downloadable\Helper\File $fileHelper
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $storageDatabase
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Downloadable\Model\Link $link,
        \Magento\Downloadable\Model\Sample $sample,
        \Magento\Downloadable\Helper\File $fileHelper,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\MediaStorage\Helper\File\Storage\Database $storageDatabase
    ) {
        parent::__construct($context);
        $this->_link = $link;
        $this->_sample = $sample;
        $this->_fileHelper = $fileHelper;
        $this->uploaderFactory = $uploaderFactory;
        $this->storageDatabase = $storageDatabase;
    }

    /**
     * Upload file controller action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $type = $this->getRequest()->getParam('type');
        $tmpPath = '';
        if ($type == 'samples') {
            $tmpPath = $this->_sample->getBaseTmpPath();
        } elseif ($type == 'links') {
            $tmpPath = $this->_link->getBaseTmpPath();
        } elseif ($type == 'link_samples') {
            $tmpPath = $this->_link->getBaseSampleTmpPath();
        }

        try {
            $uploader = $this->uploaderFactory->create(['fileId' => $type]);

            $result = $this->_fileHelper->uploadFromTmp($tmpPath, $uploader);

            if (!$result) {
                throw new \Exception('File can not be moved from temporary folder to the destination folder.');
            }

            unset($result['tmp_name'], $result['path']);

            if (isset($result['file'])) {
                $relativePath = rtrim($tmpPath, '/') . '/' . ltrim($result['file'], '/');
                $this->storageDatabase->saveFile($relativePath);
            }

        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
