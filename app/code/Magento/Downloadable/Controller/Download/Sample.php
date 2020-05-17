<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Controller\Download;

use Magento\Downloadable\Helper\Download as DownloadHelper;
use Magento\Downloadable\Model\RelatedProductRetriever;
use Magento\Downloadable\Model\Sample as SampleModel;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;

/**
 * Class Sample executes download sample action.
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Sample extends \Magento\Downloadable\Controller\Download
{
    /**
     * @var RelatedProductRetriever
     */
    private $relatedProductRetriever;

    /**
     * @param Context $context
     * @param RelatedProductRetriever $relatedProductRetriever
     */
    public function __construct(
        Context $context,
        RelatedProductRetriever $relatedProductRetriever
    ) {
        parent::__construct($context);

        $this->relatedProductRetriever = $relatedProductRetriever;
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
        $sample = $this->_objectManager->create(SampleModel::class);
        $sample->load($sampleId);
        if ($sample->getId() && $this->isProductSalable($sample)) {
            $resource = '';
            $resourceType = '';
            if ($sample->getSampleType() == DownloadHelper::LINK_TYPE_URL) {
                $resource = $sample->getSampleUrl();
                $resourceType = DownloadHelper::LINK_TYPE_URL;
            } elseif ($sample->getSampleType() == DownloadHelper::LINK_TYPE_FILE) {
                /** @var \Magento\Downloadable\Helper\File $helper */
                $helper = $this->_objectManager->get(\Magento\Downloadable\Helper\File::class);
                $resource = $helper->getFilePath($sample->getBasePath(), $sample->getSampleFile());
                $resourceType = DownloadHelper::LINK_TYPE_FILE;
            }
            try {
                $this->_processDownload($resource, $resourceType);
                // phpcs:ignore Magento2.Security.LanguageConstruct.ExitUsage
                exit(0);
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Sorry, there was an error getting requested content. Please contact the store owner.')
                );
            }
        }

        return $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
    }

    /**
     * Check is related product salable.
     *
     * @param SampleModel $sample
     * @return bool
     */
    private function isProductSalable(SampleModel $sample): bool
    {
        $product = $this->relatedProductRetriever->getProduct((int) $sample->getProductId());
        return $product ? $product->isSalable() : false;
    }
}
