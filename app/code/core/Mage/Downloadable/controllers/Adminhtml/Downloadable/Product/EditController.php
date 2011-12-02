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

require_once 'Mage/Adminhtml/controllers/Catalog/ProductController.php';

/**
 * Adminhtml downloadable product edit
 *
 * @category    Mage
 * @package     Mage_Downloadable
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Downloadable_Adminhtml_Downloadable_Product_EditController extends Mage_Adminhtml_Catalog_ProductController
{

    /**
     * Varien class constructor
     *
     */
    protected function _construct()
    {
        $this->setUsedModuleName('Mage_Downloadable');
    }

    /**
     * Load downloadable tab fieldsets
     *
     */
    public function formAction()
    {
        $this->_initProduct();
        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock(
                     'Mage_Downloadable_Block_Adminhtml_Catalog_Product_Edit_Tab_Downloadable',
                    'admin.product.downloadable.information')
                ->toHtml()
        );
    }

    /**
     * Download process
     *
     * @param string $resource
     * @param string $resourceType
     */
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
     * Download link action
     *
     */
    public function linkAction()
    {
        $linkId = $this->getRequest()->getParam('id', 0);
        $link = Mage::getModel('Mage_Downloadable_Model_Link')->load($linkId);
        if ($link->getId()) {
            $resource = '';
            $resourceType = '';
            if ($link->getLinkType() == Mage_Downloadable_Helper_Download::LINK_TYPE_URL) {
                $resource = $link->getLinkUrl();
                $resourceType = Mage_Downloadable_Helper_Download::LINK_TYPE_URL;
            } elseif ($link->getLinkType() == Mage_Downloadable_Helper_Download::LINK_TYPE_FILE) {
                $resource = Mage::helper('Mage_Downloadable_Helper_File')->getFilePath(
                    Mage_Downloadable_Model_Link::getBasePath(), $link->getLinkFile()
                );
                $resourceType = Mage_Downloadable_Helper_Download::LINK_TYPE_FILE;
            }
            try {
                $this->_processDownload($resource, $resourceType);
            } catch (Mage_Core_Exception $e) {
                $this->_getCustomerSession()->addError(Mage::helper('Mage_Downloadable_Helper_Data')->__('An error occurred while getting the requested content.'));
            }
        }
        exit(0);
    }

}
