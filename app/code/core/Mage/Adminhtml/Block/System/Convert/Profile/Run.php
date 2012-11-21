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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Convert profiles run block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_System_Convert_Profile_Run extends Mage_Adminhtml_Block_Abstract
{
    /**
     * Flag for batch model
     * @var boolean
     */
    protected $_batchModelPrepared = false;
    /**
     * Batch model instance
     * @var Mage_Dataflow_Model_Batch
     */
    protected $_batchModel = null;
    /**
     * Preparing batch model (initialization)
     * @return Mage_Adminhtml_Block_System_Convert_Profile_Run
     */
    protected function _prepareBatchModel()
    {
        if ($this->_batchModelPrepared) {
            return $this;
        }
        $this->setShowFinished(true);
        $batchModel = Mage::getSingleton('Mage_Dataflow_Model_Batch');
        $this->_batchModel = $batchModel;
        if ($batchModel->getId()) {
            if ($batchModel->getAdapter()) {
                $this->setBatchModelHasAdapter(true);
                $numberOfRecords = $this->getProfile()->getData('gui_data/import/number_of_records');
                if (!$numberOfRecords) {
                    $batchParams = $batchModel->getParams();
                    $numberOfRecords = isset($batchParams['number_of_records']) ? $batchParams['number_of_records'] : 1;
                }
                $this->setNumberOfRecords($numberOfRecords);
                $this->setShowFinished(false);
                $batchImportModel = $batchModel->getBatchImportModel();
                $importIds = $batchImportModel->getIdCollection();
                $this->setBatchItemsCount(count($importIds));
                $this->setBatchConfig(
                    array(
                        'styles' => array(
                            'error' => array(
                                'icon' => Mage::getDesign()->getViewFileUrl('Mage_Adminhtml::images/error_msg_icon.gif'),
                                'bg'   => '#FDD'
                            ),
                            'message' => array(
                                'icon' => Mage::getDesign()->getViewFileUrl('Mage_Adminhtml::images/fam_bullet_success.gif'),
                                'bg'   => '#DDF'
                            ),
                            'loader'  => Mage::getDesign()->getViewFileUrl('images/ajax-loader.gif')
                        ),
                        'template' => '<li style="#{style}" id="#{id}">'
                                    . '<img id="#{id}_img" src="#{image}" class="v-middle" style="margin-right:5px"/>'
                                    . '<span id="#{id}_status" class="text">#{text}</span>'
                                    . '</li>',
                        'text'     => $this->__('Processed <strong>%s%% %s/%d</strong> records', '#{percent}', '#{updated}', $this->getBatchItemsCount()),
                        'successText'  => $this->__('Imported <strong>%s</strong> records', '#{updated}')
                    )
                );
                $jsonIds = array_chunk($importIds, $numberOfRecords);
                $importData = array();
                foreach ($jsonIds as $part => $ids) {
                    $importData[] = array(
                        'batch_id'   => $batchModel->getId(),
                        'rows[]'     => $ids
                    );
                }
                $this->setImportData($importData);
            } else {
                $this->setBatchModelHasAdapter(false);
                $batchModel->delete();
            }
        }
        $this->_batchModelPrepared = true;
        return $this;
    }
    /**
     * Return a batch model instance
     * @return Mage_Dataflow_Model_Batch
     */
    protected function _getBatchModel()
    {
        return $this->_batchModel;
    }
    /**
     * Return a batch model config JSON
     * @return string
     */
    public function getBatchConfigJson()
    {
        return Mage::helper('Mage_Core_Helper_Data')->jsonEncode(
            $this->getBatchConfig()
        );
    }
    /**
     * Encoding to JSON
     * @param string $source
     * @return string JSON
     */
    public function jsonEncode($source)
    {
        return Mage::helper('Mage_Core_Helper_Data')->jsonEncode($source);
    }
    /**
     * Get a profile
     * @return object
     */
    public function getProfile()
    {
        return Mage::registry('current_convert_profile');
    }
    /**
     * Generating form key
     * @return string
     */
    public function getFormKey()
    {
        return Mage::getSingleton('Mage_Core_Model_Session')->getFormKey();
    }
    /**
     * Return batch model and initialize it if need
     * @return Mage_Dataflow_Model_Batch
     */
    public function getBatchModel()
    {
        return $this->_prepareBatchModel()
            ->_getBatchModel();
    }
    /**
     * Generating exceptions data
     * @return array
     */
    public function getExceptions()
    {
        if (!is_null(parent::getExceptions()))
            return parent::getExceptions();
        $exceptions = array();
        $this->getProfile()->run();
        foreach ($this->getProfile()->getExceptions() as $e) {
                switch ($e->getLevel()) {
                    case Varien_Convert_Exception::FATAL:
                        $img = 'error_msg_icon.gif';
                        $liStyle = 'background-color:#FBB; ';
                        break;
                    case Varien_Convert_Exception::ERROR:
                        $img = 'error_msg_icon.gif';
                        $liStyle = 'background-color:#FDD; ';
                        break;
                    case Varien_Convert_Exception::WARNING:
                        $img = 'fam_bullet_error.gif';
                        $liStyle = 'background-color:#FFD; ';
                        break;
                    case Varien_Convert_Exception::NOTICE:
                        $img = 'fam_bullet_success.gif';
                        $liStyle = 'background-color:#DDF; ';
                        break;
                }
                $exceptions[] = array(
                    "style"     => $liStyle,
                    "src"       => Mage::getDesign()->getViewFileUrl('Mage_Adminhtml::images/'.$img),
                    "message"   => $e->getMessage(),
                    "position" => $e->getPosition()
                );
        }
        parent::setExceptions($exceptions);
        return $exceptions;
    }
}
