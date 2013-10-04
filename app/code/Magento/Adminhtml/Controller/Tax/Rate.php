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
 * @package     Magento_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml tax rate controller
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

namespace Magento\Adminhtml\Controller\Tax;

class Rate extends \Magento\Adminhtml\Controller\Action
{
    /**
     * Show Main Grid
     *
     */
    public function indexAction()
    {
        $this->_title(__('Tax Zones and Rates'));

        $this->_initAction()
            ->_addBreadcrumb(__('Manage Tax Rates'), __('Manage Tax Rates'));
        $this ->renderLayout();
    }

    /**
     * Show Add Form
     *
     */
    public function addAction()
    {
        $rateModel = $this->_objectManager->get('Magento\Tax\Model\Calculation\Rate')
            ->load(null);

        $this->_title(__('Tax Zones and Rates'));

        $this->_title(__('New Tax Rate'));

        $rateModel->setData($this->_objectManager->get('Magento\Adminhtml\Model\Session')->getFormData(true));

        if ($rateModel->getZipIsRange() && !$rateModel->hasTaxPostcode()) {
            $rateModel->setTaxPostcode($rateModel->getZipFrom() . '-' . $rateModel->getZipTo());
        }

        $this->_initAction()
            ->_addBreadcrumb(__('Manage Tax Rates'), __('Manage Tax Rates'), $this->getUrl('*/tax_rate'))
            ->_addBreadcrumb(__('New Tax Rate'), __('New Tax Rate'))
            ->_addContent(
                $this->getLayout()->createBlock('Magento\Adminhtml\Block\Tax\Rate\Toolbar\Save')
                ->assign('header', __('Add New Tax Rate'))
                ->assign('form',
                    $this->getLayout()->createBlock('Magento\Adminhtml\Block\Tax\Rate\Form', 'tax_rate_form')
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
                $rateModel = $this->_objectManager->get('Magento\Tax\Model\Calculation\Rate')->load($rateId);
                if (!$rateModel->getId()) {
                    unset($ratePost['tax_calculation_rate_id']);
                }
            }

            $rateModel = $this->_objectManager->create('Magento\Tax\Model\Calculation\Rate')->setData($ratePost);

            try {
                $rateModel->save();

                $this->_objectManager->get('Magento\Adminhtml\Model\Session')->addSuccess(__('The tax rate has been saved.'));
                $this->getResponse()->setRedirect($this->getUrl("*/*/"));
                return true;
            } catch (\Magento\Core\Exception $e) {
                $this->_objectManager->get('Magento\Adminhtml\Model\Session')->setFormData($ratePost);
                $this->_objectManager->get('Magento\Adminhtml\Model\Session')->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_objectManager->get('Magento\Adminhtml\Model\Session')->addError($e->getMessage());
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
            $rate = $this->_objectManager->create('Magento\Tax\Model\Calculation\Rate')
                ->setData($rateData)
                ->save();
            $responseContent = $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode(array(
                'success' => true,
                'error_message' => '',
                'tax_calculation_rate_id' => $rate->getId(),
                'code' => $rate->getCode(),
            ));
        } catch (\Magento\Core\Exception $e) {
            $responseContent = $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode(array(
                'success' => false,
                'error_message' => $e->getMessage(),
                'tax_calculation_rate_id' => '',
                'code' => '',
            ));
        } catch (\Exception $e) {
            $responseContent = $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode(array(
                'success' => false,
                'error_message' => __('Something went wrong saving this rate.'),
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
        $this->_title(__('Tax Zones and Rates'));

        $rateId = (int)$this->getRequest()->getParam('rate');
        $rateModel = $this->_objectManager->get('Magento\Tax\Model\Calculation\Rate')->load($rateId);
        if (!$rateModel->getId()) {
            $this->getResponse()->setRedirect($this->getUrl("*/*/"));
            return;
        }

        if ($rateModel->getZipIsRange() && !$rateModel->hasTaxPostcode()) {
            $rateModel->setTaxPostcode($rateModel->getZipFrom() . '-' . $rateModel->getZipTo());
        }

        $this->_title(sprintf("%s", $rateModel->getCode()));

        $this->_initAction()
            ->_addBreadcrumb(__('Manage Tax Rates'), __('Manage Tax Rates'), $this->getUrl('*/tax_rate'))
            ->_addBreadcrumb(__('Edit Tax Rate'), __('Edit Tax Rate'))
            ->_addContent(
                $this->getLayout()->createBlock('Magento\Adminhtml\Block\Tax\Rate\Toolbar\Save')
                ->assign('header', __('Edit Tax Rate'))
                ->assign('form',
                    $this->getLayout()->createBlock('Magento\Adminhtml\Block\Tax\Rate\Form', 'tax_rate_form')
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
            $rateModel = $this->_objectManager->create('Magento\Tax\Model\Calculation\Rate')->load($rateId);
            if ($rateModel->getId()) {
                try {
                    $rateModel->delete();

                    $this->_objectManager->get('Magento\Adminhtml\Model\Session')
                        ->addSuccess(__('The tax rate has been deleted.'));
                    $this->getResponse()->setRedirect($this->getUrl("*/*/"));
                    return true;
                }
                catch (\Magento\Core\Exception $e) {
                    $this->_objectManager->get('Magento\Adminhtml\Model\Session')
                        ->addError($e->getMessage());
                }
                catch (\Exception $e) {
                    $this->_objectManager->get('Magento\Adminhtml\Model\Session')
                        ->addError(__('Something went wrong deleting this rate.'));
                }
                if ($referer = $this->getRequest()->getServer('HTTP_REFERER')) {
                    $this->getResponse()->setRedirect($referer);
                }
                else {
                    $this->getResponse()->setRedirect($this->getUrl("*/*/"));
                }
            } else {
                $this->_objectManager->get('Magento\Adminhtml\Model\Session')
                    ->addError(__('Something went wrong deleting this rate because of an incorrect rate ID.'));
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
            $rate = $this->_objectManager->create('Magento\Tax\Model\Calculation\Rate')->load($rateId);
            $rate->delete();
            $responseContent = $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode(array(
                'success' => true,
                'error_message' => ''
            ));
        } catch (\Magento\Core\Exception $e) {
            $responseContent = $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode(array(
                'success' => false,
                'error_message' => $e->getMessage()
            ));
        } catch (\Exception $e) {
            $responseContent = $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode(array(
                'success' => false,
                'error_message' => __('An error occurred while deleting this tax rate.')
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
     * @return \Magento\Adminhtml\Controller\Action
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('Magento_Tax::sales_tax_rates')
            ->_addBreadcrumb(__('Sales'), __('Sales'))
            ->_addBreadcrumb(__('Tax'), __('Tax'));
        return $this;
    }

    /**
     * Import and export Page
     *
     */
    public function importExportAction()
    {
        $this->_title(__('Tax Zones and Rates'));

        $this->_title(__('Import and Export Tax Rates'));

        $this->loadLayout()
            ->_setActiveMenu('Magento_Tax::system_convert_tax')
            ->_addContent($this->getLayout()->createBlock('Magento\Adminhtml\Block\Tax\Rate\ImportExportHeader'))
            ->_addContent($this->getLayout()->createBlock('Magento\Adminhtml\Block\Tax\Rate\ImportExport'))
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
                /** @var $importHandler \Magento\Tax\Model\Rate\CsvImportHandler */
                $importHandler = $this->_objectManager->create('Magento\Tax\Model\Rate\CsvImportHandler');
                $importHandler->importFromCsvFile($this->getRequest()->getFiles('import_rates_file'));

                $this->_objectManager->get('Magento\Adminhtml\Model\Session')
                    ->addSuccess(__('The tax rate has been imported.'));
            } catch (\Magento\Core\Exception $e) {
                $this->_objectManager->get('Magento\Adminhtml\Model\Session')
                    ->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_objectManager->get('Magento\Adminhtml\Model\Session')
                    ->addError(__('Invalid file upload attempt'));
            }
        } else {
            $this->_objectManager->get('Magento\Adminhtml\Model\Session')
                ->addError(__('Invalid file upload attempt'));
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
        $headers = new \Magento\Object(array(
            'code'         => __('Code'),
            'country_name' => __('Country'),
            'region_name'  => __('State'),
            'tax_postcode' => __('Zip/Post Code'),
            'rate'         => __('Rate'),
            'zip_is_range' => __('Zip/Post is Range'),
            'zip_from'     => __('Range From'),
            'zip_to'       => __('Range To')
        ));
        $template = '"{{code}}","{{country_name}}","{{region_name}}","{{tax_postcode}}","{{rate}}"'
                . ',"{{zip_is_range}}","{{zip_from}}","{{zip_to}}"';
        $content = $headers->toString($template);

        $storeTaxTitleTemplate       = array();
        $taxCalculationRateTitleDict = array();

        foreach ($this->_objectManager->create('Magento\Core\Model\Store')->getCollection()->setLoadDefault(false) as $store) {
            $storeTitle = 'title_' . $store->getId();
            $content   .= ',"' . $store->getCode() . '"';
            $template  .= ',"{{' . $storeTitle . '}}"';
            $storeTaxTitleTemplate[$storeTitle] = null;
        }
        unset($store);

        $content .= "\n";

        foreach ($this->_objectManager->create('Magento\Tax\Model\Calculation\Rate\Title')->getCollection() as $title) {
            $rateId = $title->getTaxCalculationRateId();

            if (! array_key_exists($rateId, $taxCalculationRateTitleDict)) {
                $taxCalculationRateTitleDict[$rateId] = $storeTaxTitleTemplate;
            }

            $taxCalculationRateTitleDict[$rateId]['title_' . $title->getStoreId()] = $title->getValue();
        }
        unset($title);

        $collection = $this->_objectManager->create('Magento\Tax\Model\Resource\Calculation\Rate\Collection')
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
                return $this->_authorization->isAllowed('Magento_Tax::import_export');
                break;

            case 'index':
                return $this->_authorization->isAllowed('Magento_Tax::manage_tax');
                break;

            case 'importPost':
            case 'exportPost':
                return $this->_authorization->isAllowed('Magento_Tax::manage_tax')
                    || $this->_authorization->isAllowed('Magento_Tax::import_export');
                break;

            default:
                return $this->_authorization->isAllowed('Magento_Tax::manage_tax');
                break;
        }
    }
}
