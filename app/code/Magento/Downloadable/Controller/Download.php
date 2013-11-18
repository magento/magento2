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
 * @category    Magento
 * @package     Magento_Downloadable
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Download controller
 *
 * @category    Magento
 * @package     Magento_Downloadable
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Downloadable\Controller;

class Download extends \Magento\Core\Controller\Front\Action
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

    protected function _processDownload($resource, $resourceType)
    {
        /* @var $helper \Magento\Downloadable\Helper\Download */
        $helper = $this->_objectManager->get('Magento\Downloadable\Helper\Download');

        $helper->setResource($resource, $resourceType);
        $fileName = $helper->getFilename();
        $contentType = $helper->getContentType();

        $this->getResponse()
            ->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', $contentType, true);

        if ($fileSize = $helper->getFilesize()) {
            $this->getResponse()
                ->setHeader('Content-Length', $fileSize);
        }

        if ($contentDisposition = $helper->getContentDisposition()) {
            $this->getResponse()
                ->setHeader('Content-Disposition', $contentDisposition . '; filename='.$fileName);
        }

        $this->getResponse()
            ->clearBody();
        $this->getResponse()
            ->sendHeaders();

        $helper->output();
    }

    /**
     * Download sample action
     *
     */
    public function sampleAction()
    {
        $sampleId = $this->getRequest()->getParam('sample_id', 0);
        /** @var \Magento\Downloadable\Model\Sample $sample */
        $sample = $this->_objectManager->create('Magento\Downloadable\Model\Sample')->load($sampleId);
        if ($sample->getId()) {
            $resource = '';
            $resourceType = '';
            if ($sample->getSampleType() == \Magento\Downloadable\Helper\Download::LINK_TYPE_URL) {
                $resource = $sample->getSampleUrl();
                $resourceType = \Magento\Downloadable\Helper\Download::LINK_TYPE_URL;
            } elseif ($sample->getSampleType() == \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE) {
                /** @var \Magento\Downloadable\Helper\File $helper */
                $helper = $this->_objectManager->get('Magento\Downloadable\Helper\File');
                $resource = $helper->getFilePath(
                    $sample->getBasePath(),
                    $sample->getSampleFile()
                );
                $resourceType = \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE;
            }
            try {
                $this->_processDownload($resource, $resourceType);
                exit(0);
            } catch (\Magento\Core\Exception $e) {
                $this->_getSession()->addError(__('Sorry, there was an error getting requested content. Please contact the store owner.'));
            }
        }
        return $this->_redirectReferer();
    }

    /**
     * Download link's sample action
     *
     */
    public function linkSampleAction()
    {
        $linkId = $this->getRequest()->getParam('link_id', 0);
        $link = $this->_objectManager->create('Magento\Downloadable\Model\Link')->load($linkId);
        if ($link->getId()) {
            $resource = '';
            $resourceType = '';
            if ($link->getSampleType() == \Magento\Downloadable\Helper\Download::LINK_TYPE_URL) {
                $resource = $link->getSampleUrl();
                $resourceType = \Magento\Downloadable\Helper\Download::LINK_TYPE_URL;
            } elseif ($link->getSampleType() == \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE) {
                $resource = $this->_objectManager->get('Magento\Downloadable\Helper\File')->getFilePath(
                    $this->_getLink()->getBaseSamplePath(), $link->getSampleFile()
                );
                $resourceType = \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE;
            }
            try {
                $this->_processDownload($resource, $resourceType);
                exit(0);
            } catch (\Magento\Core\Exception $e) {
                $this->_getCustomerSession()->addError(__('Sorry, there was an error getting requested content. Please contact the store owner.'));
            }
        }
        return $this->_redirectReferer();
    }

    /**
     * Download link action
     */
    public function linkAction()
    {
        $session = $this->_getCustomerSession();

        $id = $this->getRequest()->getParam('id', 0);
        /** @var \Magento\Downloadable\Model\Link\Purchased\Item $linkPurchasedItem */
        $linkPurchasedItem = $this->_objectManager->create('Magento\Downloadable\Model\Link\Purchased\Item')
            ->load($id, 'link_hash');
        if (! $linkPurchasedItem->getId() ) {
            $session->addNotice(__("We can't find the link you requested."));
            return $this->_redirect('*/customer/products');
        }
        if (!$this->_objectManager->get('Magento\Downloadable\Helper\Data')->getIsShareable($linkPurchasedItem)) {
            $customerId = $session->getCustomerId();
            if (!$customerId) {
                /** @var \Magento\Catalog\Model\Product $product */
                $product = $this->_objectManager->create('Magento\Catalog\Model\Product')
                    ->load($linkPurchasedItem->getProductId());
                if ($product->getId()) {
                    $notice = __('Please log in to download your product or purchase <a href="%1">%2</a>.',
                        $product->getProductUrl(),
                        $product->getName()
                    );
                } else {
                    $notice = __('Please log in to download your product.');
                }
                $session->addNotice($notice);
                $session->authenticate($this);
                $session->setBeforeAuthUrl(
                    $this->_objectManager->create('Magento\Core\Model\Url')->getUrl(
                        'downloadable/customer/products/',
                        array('_secure' => true)
                    )
                );
                return ;
            }
            /** @var \Magento\Downloadable\Model\Link\Purchased $linkPurchased */
            $linkPurchased = $this->_objectManager->create('Magento\Downloadable\Model\Link\Purchased')
                ->load($linkPurchasedItem->getPurchasedId());
            if ($linkPurchased->getCustomerId() != $customerId) {
                $session->addNotice(__("We can't find the link you requested."));
                return $this->_redirect('*/customer/products');
            }
        }
        $downloadsLeft = $linkPurchasedItem->getNumberOfDownloadsBought()
            - $linkPurchasedItem->getNumberOfDownloadsUsed();

        $status = $linkPurchasedItem->getStatus();
        if ($status == \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_AVAILABLE
            && ($downloadsLeft || $linkPurchasedItem->getNumberOfDownloadsBought() == 0)
        ) {
            $resource = '';
            $resourceType = '';
            if ($linkPurchasedItem->getLinkType() == \Magento\Downloadable\Helper\Download::LINK_TYPE_URL) {
                $resource = $linkPurchasedItem->getLinkUrl();
                $resourceType = \Magento\Downloadable\Helper\Download::LINK_TYPE_URL;
            } elseif ($linkPurchasedItem->getLinkType() == \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE) {
                $resource = $this->_objectManager->get('Magento\Downloadable\Helper\File')->getFilePath(
                    $this->_getLink()->getBasePath(),
                    $linkPurchasedItem->getLinkFile()
                );
                $resourceType = \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE;
            }
            try {
                $this->_processDownload($resource, $resourceType);
                $linkPurchasedItem->setNumberOfDownloadsUsed($linkPurchasedItem->getNumberOfDownloadsUsed() + 1);

                if ($linkPurchasedItem->getNumberOfDownloadsBought() != 0 && !($downloadsLeft - 1)) {
                    $linkPurchasedItem->setStatus(\Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_EXPIRED);
                }
                $linkPurchasedItem->save();
                exit(0);
            }
            catch (\Exception $e) {
                $session->addError(
                    __('Something went wrong while getting the requested content.')
                );
            }
        } elseif ($status == \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_EXPIRED) {
            $session->addNotice(__('The link has expired.'));
        } elseif ($status == \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_PENDING
            || $status == \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_PAYMENT_REVIEW
        ) {
            $session->addNotice(__('The link is not available.'));
        } else {
            $session->addError(
                __('Something went wrong while getting the requested content.')
            );
        }
        return $this->_redirect('*/customer/products');
    }

    /**
     * @return \Magento\Downloadable\Model\Link
     */
    protected function _getLink()
    {
        return $this->_objectManager->get('Magento\Downloadable\Model\Link');
    }
}
