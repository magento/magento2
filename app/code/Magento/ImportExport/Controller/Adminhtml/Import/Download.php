<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Controller\Adminhtml\Import;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\ImportExport\Controller\Adminhtml\Import as ImportController;

/**
 * Download sample file controller
 */
class Download extends ImportController
{
    const SAMPLE_FILES_MODULE = 'Magento_ImportExport';

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    protected $readFactory;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrar
     */
    protected $componentRegistrar;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magento\ImportExport\Model\Import\SampleFileProvider
     */
    private $sampleFileProvider;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     * @param ComponentRegistrar $componentRegistrar
     * @param \Magento\ImportExport\Model\Import\SampleFileProvider|null $sampleFileProvider
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\Component\ComponentRegistrar $componentRegistrar,
        \Magento\ImportExport\Model\Import\SampleFileProvider $sampleFileProvider = null
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->readFactory = $readFactory;
        $this->componentRegistrar = $componentRegistrar;
        $this->sampleFileProvider = $sampleFileProvider
            ?: \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\ImportExport\Model\Import\SampleFileProvider::class);
    }

    /**
     * Download sample file action
     *
     * @return \Magento\Framework\Controller\Result\Raw|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $entityName = $this->getRequest()->getParam('filename');

        try {
            $fileContents = $this->sampleFileProvider->getFileContents($entityName);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $this->messageManager->addError(__('There is no sample file for this entity.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/import');

            return $resultRedirect;
        }

        $fileSize = $this->sampleFileProvider->getSize($entityName);
        $fileName = $entityName . '.csv';

        $this->fileFactory->create(
            $fileName,
            null,
            DirectoryList::VAR_DIR,
            'application/octet-stream',
            $fileSize
        );

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents($fileContents);

        return $resultRaw;
    }
}
