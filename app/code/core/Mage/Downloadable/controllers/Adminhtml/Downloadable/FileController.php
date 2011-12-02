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
 * Downloadable File upload controller
 *
 * @category    Mage
 * @package     Mage_Downloadable
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Downloadable_Adminhtml_Downloadable_FileController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Upload file controller action
     */
    public function uploadAction()
    {
        $type = $this->getRequest()->getParam('type');
        $tmpPath = '';
        if ($type == 'samples') {
            $tmpPath = Mage_Downloadable_Model_Sample::getBaseTmpPath();
        } elseif ($type == 'links') {
            $tmpPath = Mage_Downloadable_Model_Link::getBaseTmpPath();
        } elseif ($type == 'link_samples') {
            $tmpPath = Mage_Downloadable_Model_Link::getBaseSampleTmpPath();
        }
        $result = array();
        try {
            $uploader = new Mage_Core_Model_File_Uploader($type);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $result = $uploader->save($tmpPath);

            if (isset($result['file'])) {
                $fullPath = rtrim($tmpPath, DS) . DS . ltrim($result['file'], DS);
                Mage::helper('Mage_Core_Helper_File_Storage_Database')->saveFile($fullPath);
            }

            $result['cookie'] = array(
                'name'     => session_name(),
                'value'    => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path'     => $this->_getSession()->getCookiePath(),
                'domain'   => $this->_getSession()->getCookieDomain()
            );
        } catch (Exception $e) {
            $result = array('error'=>$e->getMessage(), 'errorcode'=>$e->getCode());
        }

        $this->getResponse()->setBody(Mage::helper('Mage_Core_Helper_Data')->jsonEncode($result));
    }

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('Mage_Admin_Model_Session')->isAllowed('catalog/products');
    }

}
