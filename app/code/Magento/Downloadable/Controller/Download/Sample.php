<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Downloadable\Controller\Download;

use Magento\Downloadable\Controller\Download;
use Magento\Downloadable\Helper\Download as DownloadHelper;
use Magento\Downloadable\Helper\File;
use Magento\Downloadable\Model\RelatedProductRetriever;
use Magento\Downloadable\Model\Sample as SampleModel;
use Magento\Downloadable\Model\SampleFactory;
use Magento\Framework\App\Action\Context;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResponseInterface;

/**
 * Class Sample executes download sample action.
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Sample extends Download
{
    /**
     * @var RelatedProductRetriever
     */
    private $relatedProductRetriever;

    /**
     * @var File
     */
    private $file;

    /**
     * @var SampleFactory
     */
    private $sampleFactory;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @param Context $context
     * @param RelatedProductRetriever $relatedProductRetriever
     * @param File|null $file
     * @param SampleFactory|null $sampleFactory
     * @param StockConfigurationInterface|null $stockConfiguration
     */
    public function __construct(
        Context $context,
        RelatedProductRetriever $relatedProductRetriever,
        ?File $file = null,
        ?SampleFactory $sampleFactory = null,
        ?StockConfigurationInterface $stockConfiguration = null
    ) {
        parent::__construct($context);

        $this->relatedProductRetriever = $relatedProductRetriever;
        $this->file = $file ?: ObjectManager::getInstance()->get(File::class);
        $this->sampleFactory = $sampleFactory ?: ObjectManager::getInstance()->get(SampleFactory::class);
        $this->stockConfiguration = $stockConfiguration
            ?: ObjectManager::getInstance()->get(StockConfigurationInterface::class);
    }

    /**
     * Download sample action.
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $sampleId = $this->getRequest()->getParam('sample_id', 0);
        /** @var SampleModel $sample */
        $sample = $this->sampleFactory->create();
        $sample->load($sampleId);
        if ($this->isCanDownload($sample)) {
            $this->download($sample);
        }

        return $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
    }

    /**
     * Is sample can be downloaded
     *
     * @param SampleModel $sample
     * @return bool
     */
    private function isCanDownload(SampleModel $sample): bool
    {
        $product = $this->relatedProductRetriever->getProduct((int) $sample->getProductId());
        if ($product && $sample->getId()) {
            $isProductEnabled = (int) $product->getStatus() === Status::STATUS_ENABLED;

            return $product->isSalable() || $this->stockConfiguration->isShowOutOfStock() && $isProductEnabled;
        }

        return false;
    }

    /**
     * Download process
     *
     * @param SampleModel $sample
     * @return void
     */
    private function download(SampleModel $sample): void
    {
        $resource = '';
        $resourceType = '';

        if ($sample->getSampleType() === DownloadHelper::LINK_TYPE_URL) {
            $resource = $sample->getSampleUrl();
            $resourceType = DownloadHelper::LINK_TYPE_URL;
        } elseif ($sample->getSampleType() === DownloadHelper::LINK_TYPE_FILE) {
            $resource = $this->file->getFilePath($sample->getBasePath(), $sample->getSampleFile());
            $resourceType = DownloadHelper::LINK_TYPE_FILE;
        }

        try {
            $this->_processDownload($resource, $resourceType);
            // phpcs:ignore Magento2.Security.LanguageConstruct.ExitUsage
            exit(0);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Sorry, there was an error getting requested content. Please contact the store owner.')
            );
        }
    }
}
