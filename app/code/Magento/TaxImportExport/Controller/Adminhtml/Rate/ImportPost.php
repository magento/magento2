<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\TaxImportExport\Controller\Adminhtml\Rate;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\TaxImportExport\Controller\Adminhtml\Rate;
use Magento\TaxImportExport\Model\Rate\CsvImportHandler;

class ImportPost extends Rate implements HttpPostActionInterface
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var RedirectInterface
     */
    protected $resultRedirect;

    /**
     * @param Context $context
     * @param FileFactory $fileFactory
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory
    ) {
        $this->resultRedirect = $context->getRedirect();
        parent::__construct($context, $fileFactory);
    }

    /**
     * Import action from import/export tax
     *
     * @return Redirect
     */
    public function execute()
    {
        $importRatesFile = $this->getRequest()->getFiles('import_rates_file');
        if ($this->getRequest()->isPost() && isset($importRatesFile['tmp_name'])) {
            try {
                /** @var $importHandler CsvImportHandler */
                $importHandler = $this->_objectManager->create(
                    CsvImportHandler::class
                );
                $importHandler->importFromCsvFile($importRatesFile);

                $this->messageManager->addSuccess(__('The tax rate has been imported.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Invalid file upload attempt'));
            }
        } else {
            $this->messageManager->addError(__('Invalid file upload attempt'));
        }
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $resultRedirect->setUrl($this->resultRedirect->getRedirectUrl());

        return $resultRedirect;
    }
}
