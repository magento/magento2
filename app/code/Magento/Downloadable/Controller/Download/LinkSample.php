<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Controller\Download;

use Magento\Downloadable\Helper\Download as DownloadHelper;
use Magento\Downloadable\Model\Link as LinkModel;
use Magento\Downloadable\Model\RelatedProductRetriever;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;

/**
 * Class LinkSample executes download sample link action.
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class LinkSample extends \Magento\Downloadable\Controller\Download
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
     * Download link's sample action.
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $linkId = $this->getRequest()->getParam('link_id', 0);
        /** @var LinkModel $link */
        $link = $this->_objectManager->create(LinkModel::class);
        $link->load($linkId);
        if ($link->getId() && $this->isProductSalable($link)) {
            $resource = '';
            $resourceType = '';
            if ($link->getSampleType() == DownloadHelper::LINK_TYPE_URL) {
                $resource = $link->getSampleUrl();
                $resourceType = DownloadHelper::LINK_TYPE_URL;
            } elseif ($link->getSampleType() == DownloadHelper::LINK_TYPE_FILE) {
                $resource = $this->_objectManager->get(
                    \Magento\Downloadable\Helper\File::class
                )->getFilePath(
                    $this->_getLink()->getBaseSamplePath(),
                    $link->getSampleFile()
                );
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

        return $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
    }

    /**
     * Check is related product salable.
     *
     * @param LinkModel $link
     * @return bool
     */
    private function isProductSalable(LinkModel $link): bool
    {
        $product = $this->relatedProductRetriever->getProduct((int) $link->getProductId());
        return $product ? $product->isSalable() : false;
    }
}
