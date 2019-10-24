<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Controller\Adminhtml\Downloadable\File;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\ResultFactory;
use Magento\Downloadable\Controller\Adminhtml\Downloadable\File;
use Magento\Backend\App\Action\Context;
use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Model\Sample;
use Magento\Downloadable\Helper\File as FileHelper;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;

/**
 * Upload controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Upload extends File
{
    /**
     * @var Link
     */
    protected $_link; // phpcs:ignore

    /**
     * @var Sample
     */
    protected $_sample; // phpcs:ignore

    /**
     * Downloadable file helper.
     *
     * @var FileHelper
     */
    protected $_fileHelper; // phpcs:ignore

    /**
     * @var UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var Database
     */
    private $storageDatabase;

    /**
     * @var State
     */
    private $state;

    /**
     * Construct Upload controller
     *
     * @param Context $context
     * @param Link $link
     * @param Sample $sample
     * @param FileHelper $fileHelper
     * @param UploaderFactory $uploaderFactory
     * @param Database $storageDatabase
     * @param State $state
     */
    public function __construct(
        Context $context,
        Link $link,
        Sample $sample,
        FileHelper $fileHelper,
        UploaderFactory $uploaderFactory,
        Database $storageDatabase,
        State $state = null
    ) {
        parent::__construct($context);
        $this->_link = $link;
        $this->_sample = $sample;
        $this->_fileHelper = $fileHelper;
        $this->uploaderFactory = $uploaderFactory;
        $this->storageDatabase = $storageDatabase;
        $this->state = $state ? $state : ObjectManager::getInstance()->get(State::class);
    }

    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function dispatch(RequestInterface $request)
    {
        if ($this->state->getAreaCode() !== 'adminhtml') {
            return $this->_redirect($this->_redirect->getRefererUrl());
        }

        return parent::dispatch($request);
    }

    /**
     * Upload file controller action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $type = $this->getRequest()->getParam('type');
            $tmpPath = '';
            if ($type === 'samples') {
                $tmpPath = $this->_sample->getBaseTmpPath();
            } elseif ($type === 'links') {
                $tmpPath = $this->_link->getBaseTmpPath();
            } elseif ($type === 'link_samples') {
                $tmpPath = $this->_link->getBaseSampleTmpPath();
            } else {
                throw new LocalizedException(__('Upload type can not be determined.'));
            }

            $uploader = $this->uploaderFactory->create(['fileId' => $type]);

            $result = $this->_fileHelper->uploadFromTmp($tmpPath, $uploader);

            if (!$result) {
                throw new FileSystemException(
                    __('File can not be moved from temporary folder to the destination folder.')
                );
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
