<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Controller\Adminhtml\Import;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\ImportExport\Controller\Adminhtml\Import as ImportController;
use Magento\ImportExport\Model\Import\SampleFileProvider;

/**
 * Download sample file controller
 */
class Download extends ImportController implements HttpGetActionInterface
{
    const SAMPLE_FILES_MODULE = 'Magento_ImportExport';

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var ReadFactory
     */
    protected $readFactory;

    /**
     * @var ComponentRegistrar
     */
    protected $componentRegistrar;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var SampleFileProvider
     */
    private $sampleFileProvider;

    /**
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param RawFactory $resultRawFactory
     * @param ReadFactory $readFactory
     * @param ComponentRegistrar $componentRegistrar
     * @param SampleFileProvider|null $sampleFileProvider
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        RawFactory $resultRawFactory,
        ReadFactory $readFactory,
        ComponentRegistrar $componentRegistrar,
        SampleFileProvider $sampleFileProvider = null
    ) {
        parent::__construct(
            $context
        );
        $this->fileFactory = $fileFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->readFactory = $readFactory;
        $this->componentRegistrar = $componentRegistrar;
        $this->sampleFileProvider = $sampleFileProvider
            ?: ObjectManager::getInstance()
            ->get(SampleFileProvider::class);
    }

    /**
     * Download sample file action
     *
     * @return Raw
     */
    public function execute()
    {
        $entityName = $this->getRequest()->getParam('filename');

        if (preg_match('/^\w+$/', $entityName) == 0) {
            $this->messageManager->addErrorMessage(__('Incorrect entity name.'));

            return $this->getResultRedirect();
        }
        try {
            $fileContents = $this->sampleFileProvider->getFileContents($entityName);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('There is no sample file for this entity.'));

            return $this->getResultRedirect();
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

        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents($fileContents);

        return $resultRaw;
    }

    /**
     * Get redirect result
     *
     * @return Redirect
     */
    private function getResultRedirect(): Redirect
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/import');

        return $resultRedirect;
    }
}
