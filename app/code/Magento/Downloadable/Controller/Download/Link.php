<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Controller\Download;

use Magento\Downloadable\Helper\Download as DownloadHelper;
use Magento\Downloadable\Model\Link\Purchased\Item as PurchasedLink;
use Magento\Framework\App\ResponseInterface;

class Link extends \Magento\Downloadable\Controller\Download
{
    /**
     * Return customer session object
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getCustomerSession()
    {
        return $this->_objectManager->get(\Magento\Customer\Model\Session::class);
    }

    /**
     * Download link action
     *
     * @return void|ResponseInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function execute()
    {
        $session = $this->_getCustomerSession();

        $id = $this->getRequest()->getParam('id', 0);
        /** @var PurchasedLink $linkPurchasedItem */
        $linkPurchasedItem = $this->_objectManager->create(
            \Magento\Downloadable\Model\Link\Purchased\Item::class
        )->load(
            $id,
            'link_hash'
        );
        if (!$linkPurchasedItem->getId()) {
            $this->messageManager->addNotice(__("We can't find the link you requested."));
            return $this->_redirect('*/customer/products');
        }
        if (!$this->_objectManager->get(\Magento\Downloadable\Helper\Data::class)->getIsShareable($linkPurchasedItem)) {
            $customerId = $session->getCustomerId();
            if (!$customerId) {
                /** @var \Magento\Catalog\Model\Product $product */
                $product = $this->_objectManager->create(
                    \Magento\Catalog\Model\Product::class
                )->load(
                    $linkPurchasedItem->getProductId()
                );
                if ($product->getId()) {
                    $notice = __(
                        'Please sign in to download your product or purchase <a href="%1">%2</a>.',
                        $product->getProductUrl(),
                        $product->getName()
                    );
                } else {
                    $notice = __('Please sign in to download your product.');
                }
                $this->messageManager->addNotice($notice);
                $session->authenticate();
                $session->setBeforeAuthUrl(
                    $this->_objectManager->create(
                        \Magento\Framework\UrlInterface::class
                    )->getUrl(
                        'downloadable/customer/products/',
                        ['_secure' => true]
                    )
                );
                return;
            }
            /** @var \Magento\Downloadable\Model\Link\Purchased $linkPurchased */
            $linkPurchased = $this->_objectManager->create(
                \Magento\Downloadable\Model\Link\Purchased::class
            )->load(
                $linkPurchasedItem->getPurchasedId()
            );
            if ($linkPurchased->getCustomerId() != $customerId) {
                $this->messageManager->addNotice(__("We can't find the link you requested."));
                return $this->_redirect('*/customer/products');
            }
        }
        $downloadsLeft = $linkPurchasedItem->getNumberOfDownloadsBought() -
            $linkPurchasedItem->getNumberOfDownloadsUsed();

        $status = $linkPurchasedItem->getStatus();
        if ($status == PurchasedLink::LINK_STATUS_AVAILABLE && ($downloadsLeft ||
            $linkPurchasedItem->getNumberOfDownloadsBought() == 0)
        ) {
            $resource = '';
            $resourceType = '';
            if ($linkPurchasedItem->getLinkType() == DownloadHelper::LINK_TYPE_URL) {
                $resource = $linkPurchasedItem->getLinkUrl();
                $resourceType = DownloadHelper::LINK_TYPE_URL;
            } elseif ($linkPurchasedItem->getLinkType() == DownloadHelper::LINK_TYPE_FILE) {
                $resource = $this->_objectManager->get(
                    \Magento\Downloadable\Helper\File::class
                )->getFilePath(
                    $this->_getLink()->getBasePath(),
                    $linkPurchasedItem->getLinkFile()
                );
                $resourceType = DownloadHelper::LINK_TYPE_FILE;
            }
            try {
                $this->_processDownload($resource, $resourceType);
                $linkPurchasedItem->setNumberOfDownloadsUsed($linkPurchasedItem->getNumberOfDownloadsUsed() + 1);

                if ($linkPurchasedItem->getNumberOfDownloadsBought() != 0 && !($downloadsLeft - 1)) {
                    $linkPurchasedItem->setStatus(PurchasedLink::LINK_STATUS_EXPIRED);
                }
                $linkPurchasedItem->save();
                exit(0);
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Something went wrong while getting the requested content.'));
            }
        } elseif ($status == PurchasedLink::LINK_STATUS_EXPIRED) {
            $this->messageManager->addNotice(__('The link has expired.'));
        } elseif ($status == PurchasedLink::LINK_STATUS_PENDING || $status == PurchasedLink::LINK_STATUS_PAYMENT_REVIEW
        ) {
            $this->messageManager->addNotice(__('The link is not available.'));
        } else {
            $this->messageManager->addError(__('Something went wrong while getting the requested content.'));
        }
        return $this->_redirect('*/customer/products');
    }
}
