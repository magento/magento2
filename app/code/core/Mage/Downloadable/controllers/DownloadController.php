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
 * @category    Mage
 * @package     Mage_Downloadable
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Download controller
 *
 * @category    Mage
 * @package     Mage_Downloadable
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Downloadable_DownloadController extends Mage_Core_Controller_Front_Action
{

    /**
     * Return core session object
     *
     * @return Mage_Core_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('Mage_Core_Model_Session');
    }

    /**
     * Return customer session object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getCustomerSession()
    {
        return Mage::getSingleton('Mage_Customer_Model_Session');
    }

    protected function _processDownload($resource, $resourceType)
    {
        $helper = Mage::helper('Mage_Downloadable_Helper_Download');
        /* @var $helper Mage_Downloadable_Helper_Download */

        $helper->setResource($resource, $resourceType);

        $fileName       = $helper->getFilename();
        $contentType    = $helper->getContentType();

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
        $sample = Mage::getModel('Mage_Downloadable_Model_Sample')->load($sampleId);
        if ($sample->getId()) {
            $resource = '';
            $resourceType = '';
            if ($sample->getSampleType() == Mage_Downloadable_Helper_Download::LINK_TYPE_URL) {
                $resource = $sample->getSampleUrl();
                $resourceType = Mage_Downloadable_Helper_Download::LINK_TYPE_URL;
            } elseif ($sample->getSampleType() == Mage_Downloadable_Helper_Download::LINK_TYPE_FILE) {
                $resource = Mage::helper('Mage_Downloadable_Helper_File')->getFilePath(
                    Mage_Downloadable_Model_Sample::getBasePath(), $sample->getSampleFile()
                );
                $resourceType = Mage_Downloadable_Helper_Download::LINK_TYPE_FILE;
            }
            try {
                $this->_processDownload($resource, $resourceType);
                exit(0);
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError(Mage::helper('Mage_Downloadable_Helper_Data')->__('An error occurred while getting requested content. Please contact the store owner.'));
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
        $link = Mage::getModel('Mage_Downloadable_Model_Link')->load($linkId);
        if ($link->getId()) {
            $resource = '';
            $resourceType = '';
            if ($link->getSampleType() == Mage_Downloadable_Helper_Download::LINK_TYPE_URL) {
                $resource = $link->getSampleUrl();
                $resourceType = Mage_Downloadable_Helper_Download::LINK_TYPE_URL;
            } elseif ($link->getSampleType() == Mage_Downloadable_Helper_Download::LINK_TYPE_FILE) {
                $resource = Mage::helper('Mage_Downloadable_Helper_File')->getFilePath(
                    Mage_Downloadable_Model_Link::getBaseSamplePath(), $link->getSampleFile()
                );
                $resourceType = Mage_Downloadable_Helper_Download::LINK_TYPE_FILE;
            }
            try {
                $this->_processDownload($resource, $resourceType);
                exit(0);
            } catch (Mage_Core_Exception $e) {
                $this->_getCustomerSession()->addError(Mage::helper('Mage_Downloadable_Helper_Data')->__('An error occurred while getting requested content. Please contact the store owner.'));
            }
        }
        return $this->_redirectReferer();
    }

    /**
     * Download link action
     */
    public function linkAction()
    {
        $id = $this->getRequest()->getParam('id', 0);
        $linkPurchasedItem = Mage::getModel('Mage_Downloadable_Model_Link_Purchased_Item')->load($id, 'link_hash');
        if (! $linkPurchasedItem->getId() ) {
            $this->_getCustomerSession()->addNotice(Mage::helper('Mage_Downloadable_Helper_Data')->__("Requested link does not exist."));
            return $this->_redirect('*/customer/products');
        }
        if (!Mage::helper('Mage_Downloadable_Helper_Data')->getIsShareable($linkPurchasedItem)) {
            $customerId = $this->_getCustomerSession()->getCustomerId();
            if (!$customerId) {
                $product = Mage::getModel('Mage_Catalog_Model_Product')->load($linkPurchasedItem->getProductId());
                if ($product->getId()) {
                    $notice = Mage::helper('Mage_Downloadable_Helper_Data')->__(
                        'Please log in to download your product or purchase <a href="%s">%s</a>.',
                        $product->getProductUrl(), $product->getName()
                    );
                } else {
                    $notice = Mage::helper('Mage_Downloadable_Helper_Data')->__('Please log in to download your product.');
                }
                $this->_getCustomerSession()->addNotice($notice);
                $this->_getCustomerSession()->authenticate($this);
                $this->_getCustomerSession()->setBeforeAuthUrl(Mage::getUrl('downloadable/customer/products/'),
                    array('_secure' => true)
                );
                return ;
            }
            $linkPurchased = Mage::getModel('Mage_Downloadable_Model_Link_Purchased')->load($linkPurchasedItem->getPurchasedId());
            if ($linkPurchased->getCustomerId() != $customerId) {
                $this->_getCustomerSession()->addNotice(Mage::helper('Mage_Downloadable_Helper_Data')->__("Requested link does not exist."));
                return $this->_redirect('*/customer/products');
            }
        }
        $downloadsLeft = $linkPurchasedItem->getNumberOfDownloadsBought()
            - $linkPurchasedItem->getNumberOfDownloadsUsed();

        $status = $linkPurchasedItem->getStatus();
        if ($status == Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_AVAILABLE
            && ($downloadsLeft || $linkPurchasedItem->getNumberOfDownloadsBought() == 0)
        ) {
            $resource = '';
            $resourceType = '';
            if ($linkPurchasedItem->getLinkType() == Mage_Downloadable_Helper_Download::LINK_TYPE_URL) {
                $resource = $linkPurchasedItem->getLinkUrl();
                $resourceType = Mage_Downloadable_Helper_Download::LINK_TYPE_URL;
            } elseif ($linkPurchasedItem->getLinkType() == Mage_Downloadable_Helper_Download::LINK_TYPE_FILE) {
                $resource = Mage::helper('Mage_Downloadable_Helper_File')->getFilePath(
                    Mage_Downloadable_Model_Link::getBasePath(), $linkPurchasedItem->getLinkFile()
                );
                $resourceType = Mage_Downloadable_Helper_Download::LINK_TYPE_FILE;
            }
            try {
                $this->_processDownload($resource, $resourceType);
                $linkPurchasedItem->setNumberOfDownloadsUsed($linkPurchasedItem->getNumberOfDownloadsUsed() + 1);

                if ($linkPurchasedItem->getNumberOfDownloadsBought() != 0 && !($downloadsLeft - 1)) {
                    $linkPurchasedItem->setStatus(Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_EXPIRED);
                }
                $linkPurchasedItem->save();
                exit(0);
            }
            catch (Exception $e) {
                $this->_getCustomerSession()->addError(
                    Mage::helper('Mage_Downloadable_Helper_Data')->__('An error occurred while getting the requested content. Please contact the store owner.')
                );
            }
        } elseif ($status == Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_EXPIRED) {
            $this->_getCustomerSession()->addNotice(Mage::helper('Mage_Downloadable_Helper_Data')->__('The link has expired.'));
        } elseif ($status == Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_PENDING
            || $status == Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_PAYMENT_REVIEW
        ) {
            $this->_getCustomerSession()->addNotice(Mage::helper('Mage_Downloadable_Helper_Data')->__('The link is not available.'));
        } else {
            $this->_getCustomerSession()->addError(
                Mage::helper('Mage_Downloadable_Helper_Data')->__('An error occurred while getting the requested content. Please contact the store owner.')
            );
        }
        return $this->_redirect('*/customer/products');
    }

}
