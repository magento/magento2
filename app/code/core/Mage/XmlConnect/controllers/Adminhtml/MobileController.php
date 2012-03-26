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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * XmlConnect Adminhtml mobile controller
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Adminhtml_MobileController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Initialize application
     *
     * @param string $paramName
     * @param bool|string $type
     * @return Mage_XmlConnect_Model_Application
     */
    protected function _initApp($paramName = 'application_id', $type = false)
    {
        $id = (int) $this->getRequest()->getParam($paramName);
        $app = Mage::getModel('Mage_XmlConnect_Model_Application');
        if ($id) {
            $app->load($id);
            if ($app->getId()) {
                $app->loadConfiguration();
            }
        } else {
            $app->setType($type);
            Mage::register('current_app', $app);
            $app->loadDefaultConfiguration();
            Mage::unregister('current_app');
        }
        Mage::register('current_app', $app);
        return $app;
    }

    /**
     * Restore data from session $_POST and $_FILES (processed)
     *
     * @param array $data
     * @return array|null
     */
    protected function _restoreSessionFilesFormData($data)
    {
        $filesData = Mage::getSingleton('Mage_Adminhtml_Model_Session')->getUploadedFilesFormData(true);
        if (!empty($filesData) && is_array($filesData)) {
            if (!is_array($data)) {
                $data = array();
            }
            foreach ($filesData as $filePath => $fileName) {
                $target =& $data;
                Mage::helper('Mage_XmlConnect_Helper_Data')->_injectFieldToArray($target, $filePath, $fileName);
            }
        }
        return $data;
    }

    /**
     * Mobile applications management
     *
     * @return null
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('xmlconnect/mobile');
        $this->renderLayout();
    }

    /**
     * Create new app
     *
     * @return null
     */
    public function newAction()
    {
        Mage::getSingleton('Mage_Adminhtml_Model_Session')->setData('new_application', true);
        $this->loadLayout();
        $this->_setActiveMenu('xmlconnect/mobile');
        $this->renderLayout();
    }

    /**
     * Submission Action, loads application data
     *
     * @return null
     */
    public function submissionAction()
    {
        try {
            $app = $this->_initApp();
            if (!$app->getId()) {
                $this->_getSession()->addError($this->__('App does not exist.'));
                $this->_redirect('*/*/');
                return;
            }
            $app->loadSubmit();
            if ((bool) Mage::getSingleton('Mage_Adminhtml_Model_Session')->getLoadSessionFlag(true)) {
                $data = $this->_restoreSessionFilesFormData(
                    Mage::getSingleton('Mage_Adminhtml_Model_Session')->getFormSubmissionData(true)
                );
                if (!empty($data)) {
                    $app->setData(Mage::helper('Mage_XmlConnect_Helper_Data')->arrayMergeRecursive($app->getData(), $data));
                }
            }

            $this->loadLayout();
            $this->_setActiveMenu('xmlconnect/mobile');
            $this->renderLayout();
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            if (isset($app)) {
                $this->_redirect('*/*/edit', array('application_id' => $app->getId()));
            } else {
                $this->_redirect('*/*/');
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Can\'t open submission form.'));
            if (isset($app)) {
                $this->_redirect('*/*/edit', array('application_id' => $app->getId()));
            } else {
                $this->_redirect('*/*/');
            }
        }
    }

    /**
     * Edit app form
     *
     * @return null
     */
    public function editAction()
    {
        $redirectBack = false;
        try {
            $id = (int) $this->getRequest()->getParam('application_id');
            $type = $this->getRequest()->getParam('type');
            $app = $this->_initApp('application_id', $type);

            if (!$app->getId() && $id) {
                $this->_getSession()->addError($this->__('App does not exist.'));
                $this->_redirect('*/*/');
                return;
            }

            $newAppData = $this->_restoreSessionFilesFormData(
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->getFormData(true)
            );

            if (!empty($newAppData)) {
                $app->setData(Mage::helper('Mage_XmlConnect_Helper_Data')->arrayMergeRecursive($app->getData(), $newAppData));
            }

            if ($app->getId() || $app->getType()) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->setData('new_application', false);
            } else {
                $this->_redirect('*/*/new');
            }

            $devArray = Mage::helper('Mage_XmlConnect_Helper_Data')->getSupportedDevices();
            if (array_key_exists($app->getType(), $devArray)) {
                $deviceTitle = $devArray[$app->getType()];
            }
            $deviceTitle = isset($deviceTitle) ? $deviceTitle : $app->getType();
            $app->setDevtype($deviceTitle);
            $app->loadSubmit();
            $this->loadLayout();
            $this->_setActiveMenu('xmlconnect/mobile');
            $this->renderLayout();
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $redirectBack = true;
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Unable to load application form.'));
            $redirectBack = true;
            Mage::logException($e);
        }
        if ($redirectBack) {
            $this->_redirect('*/*/');
            return;
        }
    }

    /**
     * Submit POST application action
     *
     * @return null
     */
    public function submissionPostAction()
    {
        $data = $this->getRequest()->getPost();
        try {
            $isError = false;
            if (!empty($data)) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->setFormSubmissionData($this->_filterFormDataForSession($data));
            }
            /** @var $app Mage_XmlConnect_Model_Application */
            $app = $this->_initApp('key');
            $app->loadSubmit();
            $newAppData = $this->_processUploadedFiles($app->getData(), true);
            if (!empty($newAppData)) {
                $app->setData(Mage::helper('Mage_XmlConnect_Helper_Data')->arrayMergeRecursive($app->getData(), $newAppData));
            }
            $params = $app->prepareSubmitParams($data);
            $errors = $app->validateSubmit($params);
            if ($errors !== true) {
                foreach ($errors as $err) {
                    $this->_getSession()->addError($err);
                }
                $isError = true;
            }
            if (!$isError) {
                $this->_processPostRequest();
                $history = Mage::getModel('Mage_XmlConnect_Model_History');
                $history->setData(array(
                    'params' => $params, 'application_id' => $app->getId(),
                    'created_at' => Mage::getModel('Mage_Core_Model_Date')->date(), 'store_id' => $app->getStoreId(),
                    'title' => isset($params['title']) ? $params['title'] : '', 'name' => $app->getName(),
                    'code' => $app->getCode(), 'activation_key' => isset($params['resubmission_activation_key'])
                        ? $params['resubmission_activation_key'] : $params['key'],
                ));
                $history->save();
                $app->getResource()->updateApplicationStatus(
                    $app->getId(), Mage_XmlConnect_Model_Application::APP_STATUS_SUCCESS
                );
                $this->_getSession()->addSuccess($this->__('App has been submitted.'));
                $this->_clearSessionData();
                $this->_redirect('*/*/edit', array('application_id' => $app->getId()));
            } else {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->setLoadSessionFlag(true);
                $this->_redirect('*/*/submission', array('application_id' => $app->getId()));
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            if (isset($app)) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->setLoadSessionFlag(true);
                $this->_redirect('*/*/submission', array('application_id' => $app->getId()));
            } else {
                $this->_redirect('*/*/');
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Can\'t submit application.'));
            Mage::logException($e);
            if (isset($app)) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->setLoadSessionFlag(true);
                $this->_redirect('*/*/submission', array('application_id' => $app->getId()));
            } else {
                $this->_redirect('*/*/');
            }
        }
    }

    /**
     * Format post/get data for session storage
     *
     * @param array $data - $_REQUEST[]
     * @return array
     */
    protected function _filterFormDataForSession($data)
    {
        $params = null;
        if (isset($data['conf']) && is_array($data['conf'])) {
            if (isset($data['conf']['submit_text']) && is_array($data['conf']['submit_text'])) {
                $params = &$data['conf']['submit_text'];
            }
        }
        if (isset($params['country']) && is_array($params['country'])) {
            $data['conf']['submit_text']['country'] = implode(',', $params['country']);
        }
        return $data;
    }

    /**
     * Clear session data
     * Used after successful save/submit action
     *
     * @return this
     */
    protected function _clearSessionData()
    {
        Mage::getSingleton('Mage_Adminhtml_Model_Session')->unsFormData();
        Mage::getSingleton('Mage_Adminhtml_Model_Session')->unsFormSubmissionData();
        Mage::getSingleton('Mage_Adminhtml_Model_Session')->unsUploadedFilesFormData();
        return $this;
    }

    /**
     * Send HTTP POST request to magentocommerce.com
     *
     * @return null
     */
    protected function _processPostRequest()
    {
        try {
            /** @var $app Mage_XmlConnect_Model_Application */
            $app = Mage::helper('Mage_XmlConnect_Helper_Data')->getApplication();
            $params = $app->getSubmitParams();

            $appConnectorUrl = Mage::getStoreConfig('xmlconnect/mobile_application/magentocommerce_url');

            $curl = new Varien_Http_Adapter_Curl();
            $verifyPeerValue = Mage::getStoreConfig('xmlconnect/mobile_application/curl_ssl_verifypeer');
            $curl->setConfig(array(
                'timeout' => Mage_XmlConnect_Helper_Data::CURLOPT_DEFAULT_TIMEOUT,
                'verifypeer' => $verifyPeerValue, 'verifyhost' => 2, 'header' => false
            ));

            $mCommerceUrl = $appConnectorUrl . $params['key'];
            $curl->write(Zend_Http_Client::POST, $mCommerceUrl, CURL_HTTP_VERSION_1_1, array(), $params);

            $result = $curl->read();
            if(false === $result) {
                Mage::log('Curl error: ' . $curl->getError());
                $curl->close();
                Mage::throwException($this->__('Request internal error.'));
            }
            $curl->close();

            // Assert that we received an expected message in reponse.
            $resultArray = json_decode($result, true);

            $app->setResult($result);
            $success = isset($resultArray['success']) && $resultArray['success'] === true;

            $app->setSuccess($success);
            if (!$app->getSuccess()) {
                $message = isset($resultArray['message']) ? $resultArray['message']: '';
                if (is_array($message)) {
                    $message = implode(' ,', $message);
                }
                Mage::throwException($this->__('Submit App failure. %s', $message));
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Save action
     *
     * @return null
     */
    public function saveAction()
    {
        $data = $this->getRequest()->getPost();
        $redirectSubmit = $this->getRequest()->getParam('submitapp', false);
        $app = false;
        $isError = false;
        if ($data) {
            Mage::getSingleton('Mage_Adminhtml_Model_Session')->setFormData($data);
            try {
                $id = (int) $this->getRequest()->getParam('application_id');

                if (!$id && isset($data['devtype'])) {
                    $devArray = Mage::helper('Mage_XmlConnect_Helper_Data')->getSupportedDevices();
                    if (!array_key_exists($data['devtype'], $devArray)) {
                        $this->_getSession()->addError($this->__('Wrong device type.'));
                        $isError = true;
                    }
                }

                $app = $this->_initApp('application_id', $data['devtype']);
                if (!$app->getId() && $id) {
                    $this->_getSession()->addError($this->__('App does not exist.'));
                    $this->_redirect('*/*/');
                    return;
                }
                $app->addData($this->_preparePostData($data));
                $app->addData($this->_processUploadedFiles($app->getData()));
                $errors = $app->validate();

                if ($errors !== true) {
                    foreach ($errors as $err) {
                        $this->_getSession()->addError($err);
                    }
                    $isError = true;
                }

                if (!$isError) {
                    $this->_saveThemeAction($data, 'current_theme');
                    $app->save();
                    $this->_getSession()->addSuccess($this->__('App has been saved.'));
                    $this->_clearSessionData();
                }
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addException($e, $e->getMessage());
                $isError = true;
            } catch (Exception $e) {
                $this->_getSession()->addException($e, $this->__('Unable to save app.'));
                $isError = true;
                Mage::logException($e);
            }
        }
        $isApplication = $app instanceof Mage_XmlConnect_Model_Application;
        if (!$isError && $isApplication && $app->getId() && $redirectSubmit) {
            $this->_redirect('*/*/submission', array('application_id' => $app->getId()));
        } else if ($isError && $isApplication && $app->getId()) {
            Mage::getSingleton('Mage_Adminhtml_Model_Session')->setLoadSessionFlag(true);
            $this->_redirect('*/*/edit', array('application_id' => $app->getId()));
        } else if ($isError && $isApplication && !$app->getId() && $app->getType()) {
            $this->_redirect('*/*/edit', array('type' => $app->getType()));
        } else if ($this->getRequest()->getParam('back') && $isApplication) {
            $this->_redirect('*/*/edit', array('application_id' => $app->getId()));
        } else {
            $this->_redirect('*/*/');
        }
    }

    /**
     * Save changes to theme
     *
     * @param array     $data
     * @param string    $paramId
     */
    protected function _saveThemeAction($data, $paramId = 'saveTheme')
    {
        $themeName = trim($this->getRequest()->getParam($paramId, false));
        if ($themeName) {
            /** @var $themesHelper Mage_XmlConnect_Helper_Theme */
            $themesHelper = Mage::helper('Mage_XmlConnect_Helper_Theme');
            try {
                if (array_key_exists($themeName, $themesHelper->getAllThemes())) {
                    /** @var $theme Mage_XmlConnect_Model_Theme */
                    $theme = $themesHelper->getThemeByName($themeName);
                    if ($theme instanceof Mage_XmlConnect_Model_Theme) {
                        if ($paramId == 'saveTheme') {
                            $convertedConf = $this->_convertPost($data);
                        } else {
                            if (isset($data['conf'])) {
                                $convertedConf = $data['conf'];
                            } else {
                                $response = array(
                                    'error' => true,
                                    'message' => $this->__('Cannot save theme "%s". Incorrect data received', $themeName)
                                );
                            }
                        }
                        if (!isset($response)) {
                            $theme->importAndSaveData($convertedConf);
                            $response = array(
                                'message'   => $this->__('Changes have been saved to theme.'),
                                'themes'    => $themesHelper->getAllThemesArray(true),
                                'themeSelector' => $themesHelper->getThemesSelector($themeName),
                                'selectedTheme' => $themeName
                            );
                        }
                    } else {
                        $response = array('error' => true, 'message' => $this->__('Cannot load theme "%s".', $themeName));
                    }
                } else {
                    $convertedConf = $this->_convertPost($data);
                    $newTheme = $themesHelper->createNewTheme($themeName, $convertedConf);
                    $response = array(
                        'message'       => $this->__('Theme has been created.'),
                        'themes'        => $themesHelper->getAllThemesArray(true),
                        'themeSelector' => $themesHelper->getThemesSelector($newTheme->getName()),
                        'selectedTheme' => $newTheme->getName()
                    );
                }
            } catch (Mage_Core_Exception $e) {
                $response = array('error' => true, 'message' => $e->getMessage());
            } catch (Exception $e) {
                $response = array('error' => true, 'message' => $this->__('Can\'t save theme.'));
            }
        } else {
            $response = array('error' => true, 'message' => $this->__('Theme name is not set.'));
        }
        if (is_array($response)) {
            $response = Mage::helper('Mage_Core_Helper_Data')->jsonEncode($response);
            $this->getResponse()->setBody($response);
        }
    }

    /**
     * Converts native Ajax data from flat to real array
     * Convert array key->value pairs inside array like:
     * "conf_native_bar_tintcolor" => $val   to   $conf['native']['bar']['tintcolor'] => $val
     *
     * @param array $data $_POST
     * @return array
     */
    protected function _convertPost($data)
    {
        $conf = array();
        foreach ($data as $key => $val) {
            $parts = explode('_', $key);
            // "4" - is number of expected params conf_native_bar_tintcolor in correct data
            if (is_array($parts) && count($parts) == 4) {
                list(, $key1, $key2, $key3) = $parts;
                if (!isset($conf[$key1])) {
                    $conf[$key1] = array();
                }
                if (!isset($conf[$key1][$key2])) {
                    $conf[$key1][$key2] = array();
                }
                $conf[$key1][$key2][$key3] = $val;
            }
        }
        return $conf;
    }

    /**
     * Delete theme action
     *
     * @return null
     */
    public function deleteThemeAction()
    {
        $themeId = $this->getRequest()->getParam('theme_id', false);
        if ($themeId) {
            try {
                /** @var $themesHelper Mage_XmlConnect_Helper_Theme */
                $themesHelper = Mage::helper('Mage_XmlConnect_Helper_Theme');
                $result = $themesHelper->deleteTheme($themeId);
                if ($result) {
                    $response = array(
                        'message'   => $this->__('Theme has been delete.'),
                        'themes'    => $themesHelper->getAllThemesArray(true),
                        'themeSelector' => $themesHelper->getThemesSelector(),
                        'selectedTheme' => $themesHelper->getDefaultThemeName()
                    );
                } else {
                    $response = array(
                        'error'     => true,
                        'message'   => $this->__('Can\'t delete "%s" theme.', $themeId)
                    );
                }
            } catch (Mage_Core_Exception $e) {
                $response = array('error' => true, 'message'   => $e->getMessage());
            } catch (Exception $e) {
                $response = array(
                    'error'     => true,
                    'message'   => $this->__('Can\'t delete "%s" theme.', $themeId)
                );
            }
        } else {
            $response = array('error' => true, 'message' => $this->__('Theme name is not set.'));
        }
        if (is_array($response)) {
            $response = Mage::helper('Mage_Core_Helper_Data')->jsonEncode($response);
            $this->getResponse()->setBody($response);
        }
    }

    /**
     * Save Theme action
     *
     * @return null
     */
    public function saveThemeAction()
    {
        $data = $this->getRequest()->getPost();
        $this->_saveThemeAction($data);
    }

    /**
     * Save Theme action
     *
     * @return null
     */
    public function resetThemeAction()
    {
        try {
            $theme = $this->getRequest()->getPost('theme', null);
            Mage::helper('Mage_XmlConnect_Helper_Theme')->resetTheme($theme);
            $response = Mage::helper('Mage_XmlConnect_Helper_Theme')->getAllThemesArray(true);
        } catch (Mage_Core_Exception $e) {
            $response = array('error' => true, 'message' => $e->getMessage());
        } catch (Exception $e) {
            $response = array('error' => true, 'message' => $this->__('Can\'t reset theme.'));
        }
        if (is_array($response)) {
            $response = Mage::helper('Mage_Core_Helper_Data')->jsonEncode($response);
            $this->getResponse()->setBody($response);
        }
    }

    /**
     * Preview Home action handler
     *
     * @return null
     */
    public function previewHomeAction()
    {
        $this->_previewAction('preview_home_content');
    }

    /**
     * Preview Home landscape mode action handler
     */
    public function previewHomeHorAction()
    {
        $this->_previewAction('preview_home_hor_content');
    }

    /**
     * Preview Catalog action handler
     *
     * @return null
     */
    public function previewCatalogAction()
    {
        $this->_previewAction('preview_catalog_content');
    }

    /**
     * Preview Catalog landscape mode action handler
     */
    public function previewCatalogHorAction()
    {
        $this->_previewAction('preview_catalog_hor_content');
    }

    /**
     * Preview Product Info action handler
     */
    public function previewProductinfoAction()
    {
        $this->_previewAction('preview_productinfo_content');
    }

    /**
     * Preview AirMail Queue Template action handler
     */
    public function previewQueueAction()
    {
        $message = $this->_initMessage();
        if ($message->getId()) {
            $this->getRequest()->setParam('queue_preview', $message->getId());
        }
        $this->_forward('previewTemplate');
    }

    /**
     * Preview AirMail Template action handler
     */
    public function previewTemplateAction()
    {
        $this->loadLayout('adminhtml_mobile_template_preview');
        $this->renderLayout();
    }

    /**
     * Preview action implementation
     *
     * @param string $block
     */
    protected function _previewAction($block)
    {
        $redirectBack = false;
        try {
            $deviceType = $this->getRequest()->getParam('devtype');
            $app = $this->_initApp('application_id', $deviceType);
            if (!$this->getRequest()->getParam('submission_action')) {
                $app->addData($this->_preparePostData($this->getRequest()->getPost()));
            }
            /** render base configuration of application */
            $appConf = $app->getRenderConf();
            try {
                /** try to upload files */
                $dataUploaded = $this->_processUploadedFiles($app->getData());
                $app->addData($dataUploaded);
                /** render configuration with just uploaded images */
                $appConf = $app->getRenderConf();
            } catch (Exception $e) {
                /** when cannot upload - just tell user what is happen */
                $jsErrorMessage = addslashes($e->getMessage());
            }

            $this->loadLayout(false);
            $preview = $this->getLayout()->getBlock($block);

            if (isset($jsErrorMessage)) {
                $preview->setJsErrorMessage($jsErrorMessage);
            }
            $preview->setConf($appConf);
            Mage::helper('Mage_XmlConnect_Helper_Data')->getPreviewModel()->setConf($appConf);
            $this->renderLayout();
            return;
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addException($e, $e->getMessage());
            $redirectBack = true;
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Unable to process preview.'));
            $redirectBack = true;
        }
        if (isset($app) && $app instanceof Mage_XmlConnect_Model_Application && $redirectBack) {
            $this->_redirect('*/*/edit', array('application_id' => $app->getId()));
        } else {
            $this->_redirect('*/*/');
        }
    }

    /**
     * Delete app action
     *
     * @return null
     */
    public function deleteAction()
    {
        try {
            $app = $this->_initApp();
            if (!$app->getIsSubmitted()) {
                $app->delete();
                $this->_getSession()->addSuccess($this->__('App has been deleted.'));
            } else {
                Mage::throwException($this->__('It\'s not allowed to delete submitted application.'));
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addException($e, $e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Unable to find an app to delete.'));
        }
        $this->_redirect('*/*/');
    }

    /**
     * Delete template action
     */
    public function deleteTemplateAction()
    {
        // check if we know what should be deleted
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                // init template and delete
                Mage::getModel('Mage_XmlConnect_Model_Template')->load($id)->delete();

                // display success message
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess($this->__('Template has been deleted.'));

                // go to grid
                $this->_redirect('*/*/template');
                return;

            } catch (Exception $e) {
                // display error message
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($e->getMessage());
                // go back to edit form
                $this->_redirect('*/*/template', array('id' => $id));
                return;
            }
        }

        // display error message
        Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($this->__('Unable to find template to delete.'));
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('Mage_Admin_Model_Session')->isAllowed('xmlconnect');
    }

    /**
     * List application submit history
     */
    public function historyAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('xmlconnect/history');
        $this->renderLayout();
    }

    /**
     * Render apps grid
     *
     * @return null
     */
    public function gridAction()
    {
        $this->loadLayout(false);
        $this->_setActiveMenu('xmlconnect/mobile');
        $this->renderLayout();
    }

    /**
     * Process all uploaded files
     * setup file names to the configuration return array
     *
     * @param array $data
     * @param bool $restore
     * @return array
     */
    protected function _processUploadedFiles($data, $restore = false)
    {
        if ($restore === true) {
            $this->_uploadedFiles = Mage::getSingleton('Mage_Adminhtml_Model_Session')->getUploadedFilesFormDataSubmit();
        }
        if (!isset($this->_uploadedFiles) || !is_array($this->_uploadedFiles)) {
            $this->_uploadedFiles = array();
        }

        if (!empty($_FILES)) {
            foreach ($_FILES as $field => $file) {
                if (!empty($file['name']) && is_scalar($file['name'])) {
                    $uploadedFileName = Mage::helper('Mage_XmlConnect_Helper_Image')->handleUpload($field);
                    if (!empty($uploadedFileName)) {
                        $this->_uploadedFiles[$field] = $uploadedFileName;
                    }
                }
            }
        }

        foreach ($this->_uploadedFiles as $fieldPath => $fileName) {
            Mage::helper('Mage_XmlConnect_Helper_Data')->_injectFieldToArray($data, $fieldPath, $fileName);
        }
        Mage::getSingleton('Mage_Adminhtml_Model_Session')->setUploadedFilesFormData($this->_uploadedFiles);
        if ($restore === true) {
            Mage::getSingleton('Mage_Adminhtml_Model_Session')->setUploadedFilesFormDataSubmit($this->_uploadedFiles);
        }
        return $data;
    }

    /**
     * Prepare post data
     * Retains previous data in the object.
     *
     * @param array $arr
     * @return array
     */
    public function _preparePostData(array $arr)
    {
        unset($arr['code']);
        if (!empty($arr['conf']['native']['pages'])) {
            /**
             * Remove emptied pages
             */
            $pages = array();
            foreach ($arr['conf']['native']['pages'] as $_page) {
                if (!empty($_page['id']) && !empty($_page['label'])) {
                    $pages[] = $_page;
                }
            }
            $arr['conf']['native']['pages'] = $pages;
        }
        if (isset($arr['conf']['new_pages']['ids']) && isset($arr['conf']['new_pages']['labels'])) {
            $newPages = array();
            $cmsPages = &$arr['conf']['new_pages'];
            $idx = 0;
            foreach ($cmsPages['ids'] as $key => $value) {
                if (!empty($value) && !empty($cmsPages['labels'][$key])) {
                    $newPages[$idx]['id'] = trim($value);
                    $newPages[$idx++]['label'] = trim($cmsPages['labels'][$key]);
                }
            }
            if (!isset($arr['conf']['native']['pages'])) {
                $arr['conf']['native']['pages'] = array();
            }
            if (!empty($newPages)) {
                $arr['conf']['native']['pages'] = array_merge($arr['conf']['native']['pages'], $newPages);
            }
            unset($arr['conf']['new_pages']);
        }

        /**
         * Check cache settings
         */
        $lifetime = &$arr['conf']['native']['cacheLifetime'];
        $lifetime = $lifetime <= 0 ? '' : (int)$lifetime;

        /**
         * Restoring current_theme over selected but not applied theme
         */
        if (isset($arr['current_theme'])) {
            $arr['conf']['extra']['theme'] = $arr['current_theme'];
        }
        if (!isset($arr['conf']['defaultCheckout'])) {
            $arr['conf']['defaultCheckout'] = array();
        }
        if (!isset($arr['conf']['defaultCheckout']['isActive'])) {
            $arr['conf']['defaultCheckout']['isActive'] = 0;
        }

        if (!isset($arr['conf']['paypal'])) {
            $arr['conf']['paypal'] = array();
        }
        if (!isset($arr['conf']['paypal']['isActive'])) {
            $arr['conf']['paypal']['isActive'] = 0;
        }
        return $arr;
    }

    /**
     * Submission history grid action on submission history tab
     */
    public function submissionHistoryGridAction()
    {
        $this->_initApp();
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Initialize message queue
     *
     * @param string $paramName
     * @return Mage_XmlConnect_Model_Queue
     */
    protected function _initMessage($paramName = 'id')
    {
        $id = (int) $this->getRequest()->getParam($paramName);
        $message = Mage::getModel('Mage_XmlConnect_Model_Queue')->load($id);
        Mage::unregister('current_message');
        Mage::register('current_message', $message);
        return $message;
    }

    /**
     * Initialize Template object
     *
     * @param string $paramName
     * @return Mage_XmlConnect_Model_Template
     */
    protected function _initTemplate($paramName = 'id')
    {
        $id = (int) $this->getRequest()->getParam($paramName);
        $template = Mage::getModel('Mage_XmlConnect_Model_Template')->load($id);
        Mage::unregister('current_template');
        Mage::register('current_template', $template);
        return $template;
    }

    /**
     * List AirMail message queue grid
     *
     * @return null
     */
    public function queueAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('xmlconnect/queue');
        $this->renderLayout();
    }

    /**
     * Edit message action
     *
     * @return null
     */
    public function editQueueAction()
    {
        $message = $this->_initMessage();
        if ($message->getId()) {
            $this->getRequest()->setParam('template_id', $message->getTemplateId());
            $this->_initTemplate('template_id');
        }
        $this->_forward('queueMessage');
    }

    /**
     * Filtering posted data. Converting localized data if needed
     *
     * @param array $data
     * @return array
     */
    protected function _filterPostData($data)
    {
        $data = $this->_filterDateTime($data, array('exec_time'));
        return $data;
    }

    /**
     * Cancel queue action
     *
     * @return null
     */
    public function cancelQueueAction()
    {
        try {
            $id = $this->getRequest()->getParam('id');
            $message = $this->_initMessage();
            if (!$message->getId() && $id) {
                $this->_getSession()->addError($this->__('Queue does not exist.'));
                $this->_redirect('*/*/');
                return;
            }
            $message->setStatus(Mage_XmlConnect_Model_Queue::STATUS_CANCELED);
            $message->save();
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addException($e, $e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Unable to cancel queue.'));
            Mage::logException($e);
        }

        $this->_redirect('*/*/queue');
    }

    /**
     * Delete queue action
     *
     * @return null
     */
    public function deleteQueueAction()
    {
        try {
            $id = $this->getRequest()->getParam('id');
            $message = $this->_initMessage();
            if (!$message->getId() && $id) {
                $this->_getSession()->addError($this->__('Queue does not exist.'));
                $this->_redirect('*/*/');
                return;
            }
            $message->setStatus(Mage_XmlConnect_Model_Queue::STATUS_DELETED);
            $message->save();
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addException($e, $e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Unable to delete queue.'));
            Mage::logException($e);
        }

        $this->_redirect('*/*/queue');
    }

    /**
     * Cancel selected queue action
     *
     * @return null
     */
    public function massCancelQueueAction()
    {
        $queueIds = $this->getRequest()->getParam('queue');
        if(!is_array($queueIds)) {
             Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Please select message(s).'));
        } else {
            try {
                $queue = Mage::getModel('Mage_XmlConnect_Model_Queue');
                foreach ($queueIds as $queueId) {
                    $queue->reset()->load((int)$queueId)->setStatus(Mage_XmlConnect_Model_Queue::STATUS_CANCELED)
                        ->save();
                }
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess(
                    Mage::helper('Mage_Adminhtml_Helper_Data')->__('Total of %d record(s) were canceled.', count($queueIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/queue');
    }

    /**
     * Delete selected queue action
     *
     * @return null
     */
    public function massDeleteQueueAction()
    {
        $queueIds = $this->getRequest()->getParam('queue');
        if(!is_array($queueIds)) {
             Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Please select message(s).'));
        } else {
            try {
                $queue = Mage::getModel('Mage_XmlConnect_Model_Queue');
                foreach ($queueIds as $queueId) {
                    $queue->reset()->load($queueId)->setStatus(Mage_XmlConnect_Model_Queue::STATUS_DELETED)->save();
                }
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addSuccess(
                    Mage::helper('Mage_Adminhtml_Helper_Data')->__('Total of %d record(s) were deleted.', count($queueIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/queue');
    }

    /**
     * Save AirMail message action
     *
     * @return null
     */
    public function saveMessageAction()
    {
        $data         = $this->_filterPostData($this->getRequest()->getPost());
        $isError      = false;
        $message      = false;

        if ($data) {
            try {
                $data = Mage::getModel('Mage_Core_Model_Input_Filter_MaliciousCode')->filter($data);
                $template = $this->_initTemplate('template_id');
                $message = $this->_initMessage();

                if (!$template->getId() && !$message->getTemplateId()) {
                    $this->_getSession()->addError($this->__('Template for new AirMail Message does not exist.'));
                    $this->_redirect('*/*/queue');
                    return;
                }

                /** @var $app Mage_XmlConnect_Model_Application */
                $app = Mage::getModel('Mage_XmlConnect_Model_Application')->loadByCode($template->getAppCode());
                $deviceType = Mage::helper('Mage_XmlConnect_Helper_Data')->getDeviceType($app);

                if ($deviceType == Mage_XmlConnect_Helper_Data::DEVICE_TYPE_ANDROID
                    && $data['type'] == Mage_XmlConnect_Model_Queue::MESSAGE_TYPE_AIRMAIL
                ) {
                    $this->_getSession()->addError($this->__('Android doesn\'t support AirMail message type.'));
                    $redirectParams = $this->_getQueueMessageParams($message);
                    $action = $message->getId() ? 'editQueue' : 'queueMessage';
                    $this->_redirect('*/*/' . $action, $redirectParams);
                    return;
                }

                $temporaryObject = new Varien_Object();
                $temporaryObject->setData($data);

                if ($temporaryObject->getTemplateId()) {
                    $message->setTemplateId($temporaryObject->getTemplateId());
                } else {
                    $message->setTemplateId($template->getId());
                }

                if (!$message->getId()) {
                    // set status for new messages only
                    $message->setStatus(Mage_XmlConnect_Model_Queue::STATUS_IN_QUEUE);
                } elseif ($message->getStatus() != Mage_XmlConnect_Model_Queue::STATUS_IN_QUEUE) {
                    $this->_getSession()->addError(
                        $this->__('Message can be edited when status of the message is "In Queue" only.')
                    );
                    $this->_redirect('*/*/queue');
                    return;
                }

                switch ($temporaryObject->getType()) {
                    case Mage_XmlConnect_Model_Queue::MESSAGE_TYPE_AIRMAIL:
                        $message->setData('type', Mage_XmlConnect_Model_Queue::MESSAGE_TYPE_AIRMAIL);
                        break;

                    case Mage_XmlConnect_Model_Queue::MESSAGE_TYPE_PUSH:
                    default:
                        $message->setData('type', Mage_XmlConnect_Model_Queue::MESSAGE_TYPE_PUSH);
                        break;
                }
                if ($temporaryObject->getExecTime()) {
                    $execTime = Mage::getSingleton('Mage_Core_Model_Date')->gmtDate(null, $temporaryObject->getExecTime());
                    $message->setExecTime($execTime ? $execTime : Mage::getSingleton('Mage_Core_Model_Date')->gmtDate());
                } else {
                    $message->setExecTime(new Zend_Db_Expr('NULL'));
                }
                if ($template->getId()) {
                    $message->setAppCode($template->getAppCode());
                }
                $message->setPushTitle($temporaryObject->getPushTitle());
                $message->setMessageTitle($temporaryObject->getMessageTitle());
                $message->setContent($temporaryObject->getContent());
                $message->save();
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addException($e, $e->getMessage());
                $isError = true;
            } catch (Exception $e) {
                $this->_getSession()->addException($e, $this->__('Unable to save message.'));
                $isError = true;
                Mage::logException($e);
            }
        }

        if ($isError) {
            if ($isError) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->setLoadSessionFlag(true);
            }
            $redirectParams = $this->_getQueueMessageParams($message);
            $this->_redirect('*/*/queueMessage', $redirectParams);
        } else {
            $this->_redirect('*/*/queue');
        }
    }

    /**
     * Get queue message action params
     *
     * @param Mage_XmlConnect_Model_Queue $message
     * @return array
     */
    protected function _getQueueMessageParams(Mage_XmlConnect_Model_Queue $message)
    {
        $redirectParams = array();
        if ($message && $message->getId()) {
            $redirectParams['id'] = $message->getId();
        } else {
            $redirectParams['template_id'] = (int) $this->getRequest()->getParam('template_id');
        }
        return $redirectParams;
    }

    /**
     * Temlate grid
     *
     * @return null
     */
    public function templateAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('xmlconnect/template');
        $this->renderLayout();
    }

    /**
     * Create new template action
     *
     * @return null
     */
    public function newTemplateAction()
    {
        $this->_forward('editTemplate');
    }

    /**
     * Edit template action
     *
     * @return null
     */
    public function editTemplateAction()
    {
        $template = $this->_initTemplate();

        $applicationsFound = Mage::helper('Mage_XmlConnect_Helper_Data')->getApplicationOptions();
        if (!$template->getId() && empty($applicationsFound)) {
            $this->_getSession()->addError($this->__('At last one application has to be created.'));
            $this->_redirect('*/*/template');
            return;
        }

        $this->loadLayout();
        $this->_setActiveMenu('xmlconnect/templates');
        $this->renderLayout();
    }

    /**
     * Save template action
     *
     * @return null
     */
    public function saveTemplateAction()
    {
        $data = $this->getRequest()->getPost();
        $template = false;
        $isError = false;
        if ($data) {
            $data = Mage::getModel('Mage_Core_Model_Input_Filter_MaliciousCode')->filter($data);
            Mage::getSingleton('Mage_Adminhtml_Model_Session')->setTemplateFormData($data);
            try {
                $id = $this->getRequest()->getParam('id');
                $template = $this->_initTemplate();
                if (!$template->getId() && $id) {
                    $this->_getSession()->addError($this->__('Template does not exist.'));
                    $this->_redirect('*/*/');
                    return;
                }
                $template->addData($data);
                $template->save();
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addException($e, $e->getMessage());
                $isError = true;
            } catch (Exception $e) {
                $this->_getSession()->addException($e, $this->__('Unable to save template.'));
                $isError = true;
                Mage::logException($e);
            }
        }

        if ($isError && ($template && $template->getId())) {
            Mage::getSingleton('Mage_Adminhtml_Model_Session')->setLoadSessionFlag(true);
            $this->_redirect('*/*/editTemplate', array('id' => $template->getId()));
        } else {
            $this->_redirect('*/*/template');
        }
    }

    /**
     * Add message to queue action
     *
     * @return null
     */
    public function queueMessageAction()
    {
        $message = $this->_initMessage();
        if (!$message->getId()) {
            $template = $this->_initTemplate('template_id');
            if (!$template->getId()) {
                $this->_getSession()->addError($this->__('Template for new AirMail Message does not exist.'));
                $this->_redirect('*/*/template');
            }
        }

        if (isset($template)) {
            $applicationId = $template->getApplicationId();
        } else {
            $applicationId = Mage::getModel('Mage_XmlConnect_Model_Template')->load($message->getTemplateId())->getApplicationId();
        }

        /** @var $app Mage_XmlConnect_Model_Application */
        $app = Mage::getModel('Mage_XmlConnect_Model_Application')->load($applicationId);

        if(!$app->isNotificationsActive()) {
            $this->_getSession()->addError(
                $this->__('Queue is allowed only for applications with enabled Push Notification.')
            );
            $action = $message->getId() ? 'queue' : 'template';
            $this->_redirect('*/*/' . $action);
            return;
        }

        $this->loadLayout();
        if ($message->getId()) {
            $title = $this->__('Edit AirMail Message');
        } else {
            $title = $this->__('New AirMail Message');
        }
        $this->_addBreadcrumb($this->__('AirMail Message Queue'), $this->__('AirMail Message Queue'),
            $this->getUrl('*/*/queue')
        );
        $this->_addBreadcrumb($title, $title);

        $this->_setActiveMenu('xmlconnect/queue');
        $this->renderLayout();
    }

    /**
     * Edit queue message action
     *
     * @return null
     */
    public function editMessageAction()
    {
        $this->_forward('queueMessage');
    }
}
