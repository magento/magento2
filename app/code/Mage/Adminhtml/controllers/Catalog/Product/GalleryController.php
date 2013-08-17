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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog product gallery controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Catalog_Product_GalleryController extends Mage_Adminhtml_Controller_Action
{
    public function uploadAction()
    {
        try {
            $uploader = Mage::getModel('Mage_Core_Model_File_Uploader', array('fileId' => 'image'));
            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
            $imageAdapter = $this->_objectManager->get('Mage_Core_Model_Image_AdapterFactory')->create();
            $uploader->addValidateCallback('catalog_product_image', $imageAdapter, 'validateUploadFile');
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $result = $uploader->save(
                Mage::getSingleton('Mage_Catalog_Model_Product_Media_Config')->getBaseTmpMediaPath()
            );

            $this->_eventManager->dispatch('catalog_product_gallery_upload_image_after', array(
                'result' => $result,
                'action' => $this
            ));

            unset($result['tmp_name']);
            unset($result['path']);

            $result['url'] = Mage::getSingleton('Mage_Catalog_Model_Product_Media_Config')
                ->getTmpMediaUrl($result['file']);
            $result['file'] = $result['file'] . '.tmp';

        } catch (Exception $e) {
            $result = array(
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode()
            );
        }

        $this->getResponse()->setBody(Mage::helper('Mage_Core_Helper_Data')->jsonEncode($result));
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Mage_Catalog::products');
    }
} // Class Mage_Adminhtml_Catalog_Product_GalleryController End
