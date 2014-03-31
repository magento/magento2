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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Downloadable\Controller;

use Magento\App\ResponseInterface;
use Magento\Downloadable\Helper\Download as DownloadHelper;
use Magento\Model\Exception as CoreException;
use Magento\Downloadable\Model\Link\Purchased\Item as PurchasedLink;

/**
 * Download controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Download extends \Magento\App\Action\Action
{
    /**
     * Return core session object
     *
     * @return \Magento\Core\Model\Session
     */
    protected function _getSession()
    {
        return $this->_objectManager->get('Magento\Core\Model\Session');
    }

    /**
     * Return customer session object
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getCustomerSession()
    {
        return $this->_objectManager->get('Magento\Customer\Model\Session');
    }

    /**
     * Prepare response to output resource contents
     *
     * @param string $path         Path to resource
     * @param string $resourceType Type of resource (see Magento\Downloadable\Helper\Download::LINK_TYPE_* constants)
     * @return void
     */
    protected function _processDownload($path, $resourceType)
    {
        /* @var $helper DownloadHelper */
        $helper = $this->_objectManager->get('Magento\Downloadable\Helper\Download');

        $helper->setResource($path, $resourceType);
        $fileName = $helper->getFilename();
        $contentType = $helper->getContentType();

        $this->getResponse()->setHttpResponseCode(
            200
        )->setHeader(
            'Pragma',
            'public',
            true
        )->setHeader(
            'Cache-Control',
            'must-revalidate, post-check=0, pre-check=0',
            true
        )->setHeader(
            'Content-type',
            $contentType,
            true
        );

        if ($fileSize = $helper->getFileSize()) {
            $this->getResponse()->setHeader('Content-Length', $fileSize);
        }

        if ($contentDisposition = $helper->getContentDisposition()) {
            $this->getResponse()->setHeader('Content-Disposition', $contentDisposition . '; filename=' . $fileName);
        }

        $this->getResponse()->clearBody();
        $this->getResponse()->sendHeaders();

        $helper->output();
    }

    /**
     * Download sample action
     *
     * @return ResponseInterface
     */
    public function sampleAction()
    {
        $sampleId = $this->getRequest()->getParam('sample_id', 0);
        /** @var \Magento\Downloadable\Model\Sample $sample */
        $sample = $this->_objectManager->create('Magento\Downloadable\Model\Sample')->load($sampleId);
        if ($sample->getId()) {
            $resource = '';
            $resourceType = '';
            if ($sample->getSampleType() == DownloadHelper::LINK_TYPE_URL) {
                $resource = $sample->getSampleUrl();
                $resourceType = DownloadHelper::LINK_TYPE_URL;
            } elseif ($sample->getSampleType() == DownloadHelper::LINK_TYPE_FILE) {
                /** @var \Magento\Downloadable\Helper\File $helper */
                $helper = $this->_objectManager->get('Magento\Downloadable\Helper\File');
                $resource = $helper->getFilePath($sample->getBasePath(), $sample->getSampleFile());
                $resourceType = DownloadHelper::LINK_TYPE_FILE;
            }
            try {
                $this->_processDownload($resource, $resourceType);
                exit(0);
            } catch (CoreException $e) {
                $this->messageManager->addError(
                    __('Sorry, there was an error getting requested content. Please contact the store owner.')
                );
            }
        }
        return $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
    }

    /**
     * Download link's sample action
     *
     * @return ResponseInterface
     */
    public function linkSampleAction()
    {
        $linkId = $this->getRequest()->getParam('link_id', 0);
        /** @var \Magento\Downloadable\Model\Link $link */
        $link = $this->_objectManager->create('Magento\Downloadable\Model\Link')->load($linkId);
        if ($link->getId()) {
            $resource = '';
            $resourceType = '';
            if ($link->getSampleType() == DownloadHelper::LINK_TYPE_URL) {
                $resource = $link->getSampleUrl();
                $resourceType = DownloadHelper::LINK_TYPE_URL;
            } elseif ($link->getSampleType() == DownloadHelper::LINK_TYPE_FILE) {
                $resource = $this->_objectManager->get(
                    'Magento\Downloadable\Helper\File'
                )->getFilePath(
                    $this->_getLink()->getBaseSamplePath(),
                    $link->getSampleFile()
                );
                $resourceType = DownloadHelper::LINK_TYPE_FILE;
            }
            try {
                $this->_processDownload($resource, $resourceType);
                exit(0);
            } catch (CoreException $e) {
                $this->messageManager->addError(
                    __('Sorry, there was an error getting requested content. Please contact the store owner.')
                );
            }
        }
        return $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
    }

    /**
     * Download link action
     *
     * @return void|ResponseInterface
     */
    public function linkAction()
    {
        $session = $this->_getCustomerSession();

        $id = $this->getRequest()->getParam('id', 0);
        /** @var PurchasedLink $linkPurchasedItem */
        $linkPurchasedItem = $this->_objectManager->create(
            'Magento\Downloadable\Model\Link\Purchased\Item'
        )->load(
            $id,
            'link_hash'
        );
        if (!$linkPurchasedItem->getId()) {
            $this->messageManager->addNotice(__("We can't find the link you requested."));
            return $this->_redirect('*/customer/products');
        }
        if (!$this->_objectManager->get('Magento\Downloadable\Helper\Data')->getIsShareable($linkPurchasedItem)) {
            $customerId = $session->getCustomerId();
            if (!$customerId) {
                /** @var \Magento\Catalog\Model\Product $product */
                $product = $this->_objectManager->create(
                    'Magento\Catalog\Model\Product'
                )->load(
                    $linkPurchasedItem->getProductId()
                );
                if ($product->getId()) {
                    $notice = __(
                        'Please log in to download your product or purchase <a href="%1">%2</a>.',
                        $product->getProductUrl(),
                        $product->getName()
                    );
                } else {
                    $notice = __('Please log in to download your product.');
                }
                $this->messageManager->addNotice($notice);
                $session->authenticate($this);
                $session->setBeforeAuthUrl(
                    $this->_objectManager->create(
                        'Magento\UrlInterface'
                    )->getUrl(
                        'downloadable/customer/products/',
                        array('_secure' => true)
                    )
                );
                return;
            }
            /** @var \Magento\Downloadable\Model\Link\Purchased $linkPurchased */
            $linkPurchased = $this->_objectManager->create(
                'Magento\Downloadable\Model\Link\Purchased'
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
                    'Magento\Downloadable\Helper\File'
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

    /**
     * Get link model
     *
     * @return \Magento\Downloadable\Model\Link
     */
    protected function _getLink()
    {
        return $this->_objectManager->get('Magento\Downloadable\Model\Link');
    }
}
