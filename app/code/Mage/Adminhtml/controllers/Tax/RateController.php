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
 * Adminhtml tax rate controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Tax_RateController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Show Main Grid
     *
     */
    public function indexAction()
    {
        $this->_title($this->__('Tax Zones and Rates'));

        $this->_initAction()
            ->_addBreadcrumb(Mage::helper('Mage_Tax_Helper_Data')->__('Manage Tax Rates'), Mage::helper('Mage_Tax_Helper_Data')->__('Manage Tax Rates'));
        $this ->renderLayout();
    }

    /**
     * Show Add Form
     *
     */
    public function addAction()
    {
        $rateModel = Mage::getSingleton('Mage_Tax_Model_Calculation_Rate')
            ->load(null);

        $this->_title($this->__('Tax Zones and Rates'));

        $this->_title($this->__('New Tax Rate'));

        $rateModel->setData(Mage::getSingleton('Mage_Adminhtml_Model_Session')->getFormData(true));

        if ($rateModel->getZipIsRange() && !$rateModel->hasTaxPostcode()) {
            $rateModel->setTaxPostcode($rateModel->getZipFrom() . '-' . $rateModel->getZipTo());
        }

        $this->_initAction()
            ->_addBreadcrumb(Mage::helper('Mage_Tax_Helper_Data')->__('Manage Tax Rates'), Mage::helper('Mage_Tax_Helper_Data')->__('Manage Tax Rates'), $this->getUrl('*/tax_rate'))
            ->_addBreadcrumb(Mage::helper('Mage_Tax_Helper_Data')->__('New Tax Rate'), Mage::helper('Mage_Tax_Helper_Data')->__('New Tax Rate'))
            ->_addContent(
                $this->getLayout()->createBlock('Mage_Adminhtml_Block_Tax_Rate_Toolbar_Save')
                ->assign('header', Mage::helper('Mage_Tax_Helper_Data')->__('Add New Tax Rate'))
                ->assign('form',
                    $this->getLayout()->createBlock('Mage_Adminhtml_Block_Tax_Rate_Form', 'tax_rate_form')
                )
            )
            ->renderLayout();
    }

    /**
     * Save Rate and Data
     *
     * @return bool
     */
    public function saveAction()
    {
        $ratePost = $this->getRequest()->getPost();
        if ($ratePost) {
            $rateId = $this->getRequest()->getParam('tax_calculation_rate_id');
            if ($rateId) {
                $rateModel = Mage::getSingleton('Mage_Tax_Model_Calculation_Rate')->load($rateId);
                if (!$rateModel->getId()) {
                    unset($ratePost['tax_calculation_rate_id']);
                }
            }

            $rateModel = Mage::getModel('Mage_Tax_Model_Calculation_Rate')->setData($ratePost);

            try {
                $rateModel->save();

                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess(Mage::helper('Mage_Tax_Helper_Data')->__('The tax rate has been saved.'));
                $this->getResponse()->setRedirect($this->getUrl("*/*/"));
                return true;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->setFormData($ratePost);
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($e->getMessage());
            }

            $this->_redirectReferer();
            return;
        }
        $this->getResponse()->setRedirect($this->getUrl('*/tax_rate'));
    }

    /**
     * Save Tax Rate via AJAX
     */
    public function ajaxSaveAction()
    {
        $responseContent = '';
        try {
            $rateData = $this->_processRateData($this->getRequest()->getPost());
            $rate = Mage::getModel('Mage_Tax_Model_Calculation_Rate')
                ->setData($rateData)
                ->save();
            $responseContent = Mage::helper('Mage_Core_Helper_Data')->jsonEncode(array(
                'success' => true,
                'error_message' => '',
                'tax_calculation_rate_id' => $rate->getId(),
                'code' => $rate->getCode(),
            ));
        } catch (Mage_Core_Exception $e) {
            $responseContent = Mage::helper('Mage_Core_Helper_Data')->jsonEncode(array(
                'success' => false,
                'error_message' => $e->getMessage(),
                'tax_calculation_rate_id' => '',
                'code' => '',
            ));
        } catch (Exception $e) {
            $responseContent = Mage::helper('Mage_Core_Helper_Data')->jsonEncode(array(
                'success' => false,
                'error_message' => Mage::helper('Mage_Tax_Helper_Data') ->__('Something went wrong saving this rate.'),
                'tax_calculation_rate_id' => '',
                'code' => '',
            ));
        }
        $this->getResponse()->setBody($responseContent);
    }

    /**
     * Validate/Filter Rate Data
     *
     * @param array $rateData
     * @return array
     */
    protected function _processRateData($rateData)
    {
        $result = array();
        foreach ($rateData as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->_processRateData($value);
            } else {
                $result[$key] = trim(strip_tags($value));
            }
        }
        return $result;
    }

    /**
     * Show Edit Form
     *
     */
    public function editAction()
    {
        $this->_title($this->__('Tax Zones and Rates'));

        $rateId = (int)$this->getRequest()->getParam('rate');
        $rateModel = Mage::getSingleton('Mage_Tax_Model_Calculation_Rate')->load($rateId);
        if (!$rateModel->getId()) {
            $this->getResponse()->setRedirect($this->getUrl("*/*/"));
            return;
        }

        if ($rateModel->getZipIsRange() && !$rateModel->hasTaxPostcode()) {
            $rateModel->setTaxPostcode($rateModel->getZipFrom() . '-' . $rateModel->getZipTo());
        }

        $this->_title(sprintf("%s", $rateModel->getCode()));

        $this->_initAction()
            ->_addBreadcrumb(Mage::helper('Mage_Tax_Helper_Data')->__('Manage Tax Rates'), Mage::helper('Mage_Tax_Helper_Data')->__('Manage Tax Rates'), $this->getUrl('*/tax_rate'))
            ->_addBreadcrumb(Mage::helper('Mage_Tax_Helper_Data')->__('Edit Tax Rate'), Mage::helper('Mage_Tax_Helper_Data')->__('Edit Tax Rate'))
            ->_addContent(
                $this->getLayout()->createBlock('Mage_Adminhtml_Block_Tax_Rate_Toolbar_Save')
                ->assign('header', Mage::helper('Mage_Tax_Helper_Data')->__('Edit Tax Rate'))
                ->assign('form',
                    $this->getLayout()->createBlock('Mage_Adminhtml_Block_Tax_Rate_Form', 'tax_rate_form')
                        ->setShowLegend(true)
                )
            )
            ->renderLayout();
    }

    /**
     * Delete Rate and Data
     *
     * @return bool
     */
    public function deleteAction()
    {
        if ($rateId = $this->getRequest()->getParam('rate')) {
            $rateModel = Mage::getModel('Mage_Tax_Model_Calculation_Rate')->load($rateId);
            if ($rateModel->getId()) {
                try {
                    $rateModel->delete();

                    Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess(Mage::helper('Mage_Tax_Helper_Data')->__('The tax rate has been deleted.'));
                    $this->getResponse()->setRedirect($this->getUrl("*/*/"));
                    return true;
                }
                catch (Mage_Core_Exception $e) {
                    Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($e->getMessage());
                }
                catch (Exception $e) {
                    Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError(Mage::helper('Mage_Tax_Helper_Data')->__('Something went wrong deleting this rate.'));
                }
                if ($referer = $this->getRequest()->getServer('HTTP_REFERER')) {
                    $this->getResponse()->setRedirect($referer);
                }
                else {
                    $this->getResponse()->setRedirect($this->getUrl("*/*/"));
                }
            } else {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError(Mage::helper('Mage_Tax_Helper_Data')->__('Something went wrong deleting this rate because of an incorrect rate ID.'));
                $this->getResponse()->setRedirect($this->getUrl('*/*/'));
            }
        }
    }

    /**
     * Delete Tax Rate via AJAX
     */
    public function ajaxDeleteAction()
    {

        $responseContent = '';
        $rateId = (int)$this->getRequest()->getParam('tax_calculation_rate_id');
        try {
            $rate = Mage::getModel('Mage_Tax_Model_Calculation_Rate')->load($rateId);
            $rate->delete();
            $responseContent = Mage::helper('Mage_Core_Helper_Data')->jsonEncode(array(
                'success' => true,
                'error_message' => ''
            ));
        } catch (Mage_Core_Exception $e) {
            $responseContent = Mage::helper('Mage_Core_Helper_Data')->jsonEncode(array(
                'success' => false,
                'error_message' => $e->getMessage()
            ));
        } catch (Exception $e) {
            $responseContent = Mage::helper('Mage_Core_Helper_Data')->jsonEncode(array(
                'success' => false,
                'error_message' => Mage::helper('Mage_Tax_Helper_Data')->__('An error occurred while deleting this tax rate.')
            ));
        }
        $this->getResponse()->setBody($responseContent);
    }

    /**
     * Export rates grid to CSV format
     *
     */
    public function exportCsvAction()
    {
        $this->loadLayout(false);
        $content = $this->getLayout()->getChildBlock('adminhtml.tax.rate.grid','grid.export');
        $this->_prepareDownloadResponse('rates.csv', $content->getCsvFile());
    }

    /**
     * Export rates grid to XML format
     */
    public function exportXmlAction()
    {
        $this->loadLayout(false);
        $content = $this->getLayout()->getChildBlock('adminhtml.tax.rate.grid','grid.export');
        $this->_prepareDownloadResponse('rates.xml', $content->getExcelFile());
    }

    /**
     * Initialize action
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('Mage_Tax::sales_tax_rates')
            ->_addBreadcrumb(Mage::helper('Mage_Tax_Helper_Data')->__('Sales'), Mage::helper('Mage_Tax_Helper_Data')->__('Sales'))
            ->_addBreadcrumb(Mage::helper('Mage_Tax_Helper_Data')->__('Tax'), Mage::helper('Mage_Tax_Helper_Data')->__('Tax'));
        return $this;
    }

    /**
     * Import and export Page
     *
     */
    public function importExportAction()
    {
        $this->_title($this->__('Tax Zones and Rates'));

        $this->_title($this->__('Import and Export Tax Rates'));

        $this->loadLayout()
            ->_setActiveMenu('Mage_Tax::system_convert_tax')
            ->_addContent($this->getLayout()->createBlock('Mage_Adminhtml_Block_Tax_Rate_ImportExportHeader'))
            ->_addContent($this->getLayout()->createBlock('Mage_Adminhtml_Block_Tax_Rate_ImportExport'))
            ->renderLayout();
    }

    /**
     * import action from import/export tax
     *
     */
    public function importPostAction()
    {
        if ($this->getRequest()->isPost() && !empty($_FILES['import_rates_file']['tmp_name'])) {
            try {
                /** @var $importHandler Mage_Tax_Model_Rate_CsvImportHandler */
                $importHandler = $this->_objectManager->create('Mage_Tax_Model_Rate_CsvImportHandler');
                $importHandler->importFromCsvFile($this->getRequest()->getFiles('import_rates_file'));

                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess(Mage::helper('Mage_Tax_Helper_Data')->__('The tax rate has been imported.'));
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError(Mage::helper('Mage_Tax_Helper_Data')->__('Invalid file upload attempt'));
            }
        } else {
            Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError(Mage::helper('Mage_Tax_Helper_Data')->__('Invalid file upload attempt'));
        }
        $this->_redirectReferer();
    }

    /**
     * export action from import/export tax
     *
     */
    public function exportPostAction()
    {
        /** start csv content and set template */
        $headers = new Varien_Object(array(
            'code'         => Mage::helper('Mage_Tax_Helper_Data')->__('Code'),
            'country_name' => Mage::helper('Mage_Tax_Helper_Data')->__('Country'),
            'region_name'  => Mage::helper('Mage_Tax_Helper_Data')->__('State'),
            'tax_postcode' => Mage::helper('Mage_Tax_Helper_Data')->__('Zip/Post Code'),
            'rate'         => Mage::helper('Mage_Tax_Helper_Data')->__('Rate'),
            'zip_is_range' => Mage::helper('Mage_Tax_Helper_Data')->__('Zip/Post is Range'),
            'zip_from'     => Mage::helper('Mage_Tax_Helper_Data')->__('Range From'),
            'zip_to'       => Mage::helper('Mage_Tax_Helper_Data')->__('Range To')
        ));
        $template = '"{{code}}","{{country_name}}","{{region_name}}","{{tax_postcode}}","{{rate}}"'
                . ',"{{zip_is_range}}","{{zip_from}}","{{zip_to}}"';
        $content = $headers->toString($template);

        $storeTaxTitleTemplate       = array();
        $taxCalculationRateTitleDict = array();

        foreach (Mage::getModel('Mage_Core_Model_Store')->getCollection()->setLoadDefault(false) as $store) {
            $storeTitle = 'title_' . $store->getId();
            $content   .= ',"' . $store->getCode() . '"';
            $template  .= ',"{{' . $storeTitle . '}}"';
            $storeTaxTitleTemplate[$storeTitle] = null;
        }
        unset($store);

        $content .= "\n";

        foreach (Mage::getModel('Mage_Tax_Model_Calculation_Rate_Title')->getCollection() as $title) {
            $rateId = $title->getTaxCalculationRateId();

            if (! array_key_exists($rateId, $taxCalculationRateTitleDict)) {
                $taxCalculationRateTitleDict[$rateId] = $storeTaxTitleTemplate;
            }

            $taxCalculationRateTitleDict[$rateId]['title_' . $title->getStoreId()] = $title->getValue();
        }
        unset($title);

        $collection = Mage::getResourceModel('Mage_Tax_Model_Resource_Calculation_Rate_Collection')
            ->joinCountryTable()
            ->joinRegionTable();

        while ($rate = $collection->fetchItem()) {
            if ($rate->getTaxRegionId() == 0) {
                $rate->setRegionName('*');
            }

            if (array_key_exists($rate->getId(), $taxCalculationRateTitleDict)) {
                $rate->addData($taxCalculationRateTitleDict[$rate->getId()]);
            } else {
                $rate->addData($storeTaxTitleTemplate);
            }

            $content .= $rate->toString($template) . "\n";
        }
        $this->loadLayout();
        $this->_prepareDownloadResponse('tax_rates.csv', $content);
    }

    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'importExport':
                return $this->_authorization->isAllowed('Mage_Tax::import_export');
                break;

            case 'index':
                return $this->_authorization->isAllowed('Mage_Tax::manage_tax');
                break;

            case 'importPost':
            case 'exportPost':
                return $this->_authorization->isAllowed('Mage_Tax::manage_tax')
                    || $this->_authorization->isAllowed('Mage_Tax::import_export');
                break;

            default:
                return $this->_authorization->isAllowed('Mage_Tax::manage_tax');
                break;
        }

    }
}
