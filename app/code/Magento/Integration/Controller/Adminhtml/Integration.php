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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Integration\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info;
use Magento\Integration\Model\Integration as IntegrationKeyConstants;
use Magento\Integration\Exception as IntegrationException;

/**
 * Controller for integrations management.
 */
class Integration extends Action
{
    /** Param Key for extracting integration id from Request */
    const PARAM_INTEGRATION_ID = 'id';

    /** Keys used for registering data into the registry */
    const REGISTRY_KEY_CURRENT_INTEGRATION = 'current_integration';

    /** Request parameter which define the dialog window requested */
    const PARAM_DIALOG_ID = 'popup_dialog';

    /**#@+
     * Allowed values for PARAM_DIALOG_ID request parameter
     */
    const DIALOG_PERMISSIONS = 'permissions';
    const DIALOG_TOKENS = 'tokens';
    /**#@-*/

    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_registry = null;

    /** @var \Magento\Logger */
    protected $_logger;

    /** @var \Magento\Integration\Service\IntegrationV1Interface */
    private $_integrationService;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Integration\Service\IntegrationV1Interface $integrationService
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Logger $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Integration\Service\IntegrationV1Interface $integrationService,
        \Magento\Core\Model\Registry $registry,
        \Magento\Logger $logger
    ) {
        $this->_registry = $registry;
        $this->_logger = $logger;
        $this->_integrationService = $integrationService;
        parent::__construct($context);
    }

    /**
     * Integrations grid.
     */
    public function indexAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Integration::system_integrations');
        $this->_addBreadcrumb(__('Integrations'), __('Integrations'));
        $this->_title->add(__('Integrations'));
        $this->_view->renderLayout();
    }

    /**
     * AJAX integrations grid.
     */
    public function gridAction()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }

    /**
     * Check ACL.
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Integration::integrations');
    }

    /**
     * New integration action.
     */
    public function newAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Integration::system_integrations');
        $this->_addBreadcrumb(__('New Integration'), __('New Integration'));
        $this->_title->add(__('New Integration'));
        /** Try to recover integration data from session if it was added during previous request which failed. */
        $restoredIntegration = $this->_getSession()->getIntegrationData();
        if ($restoredIntegration) {
            $this->_registry->register(self::REGISTRY_KEY_CURRENT_INTEGRATION, $restoredIntegration);
            $this->_getSession()->setIntegrationData(array());
        }
        $this->_view->renderLayout();
    }

    /**
     * Edit integration action.
     */
    public function editAction()
    {
        /** Try to recover integration data from session if it was added during previous request which failed. */
        $integrationId = (int)$this->getRequest()->getParam(self::PARAM_INTEGRATION_ID);
        if ($integrationId) {
            try {
                $integrationData = $this->_integrationService->get($integrationId);
            } catch (IntegrationException $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/');
                return;
            }
            $restoredIntegration = $this->_getSession()->getIntegrationData();
            if (isset($restoredIntegration[Info::DATA_ID]) && $integrationId == $restoredIntegration[Info::DATA_ID]) {
                $integrationData = array_merge($integrationData, $restoredIntegration);
            }

            if (isset($integrationData[Info::DATA_SETUP_TYPE])
                && $integrationData[Info::DATA_SETUP_TYPE] == IntegrationKeyConstants::TYPE_CONFIG
            ) {
                //Cannot edit Integrations created from Config. No error necessary just redirect to grid
                $this->_redirect('*/*/');
                return;
            }
            $this->_registry->register(self::REGISTRY_KEY_CURRENT_INTEGRATION, $integrationData);
        } else {
            $this->_getSession()->addError(__('Integration ID is not specified or is invalid.'));
            $this->_redirect('*/*/');
            return;
        }
        $this->_view->loadLayout();
        $this->_getSession()->setIntegrationData(array());
        $this->_setActiveMenu('Magento_Integration::system_integrations');
        $this->_addBreadcrumb(
            __('Edit "%1" Integration', $integrationData[Info::DATA_NAME]),
            __('Edit "%1" Integration', $integrationData[Info::DATA_NAME])
        );
        $this->_title->add(__('Edit "%1" Integration', $integrationData[Info::DATA_NAME]));
        $this->_view->renderLayout();
    }

    /**
     * Save integration action.
     */
    public function saveAction()
    {
        /** @var array $integrationData */
        $integrationData = array();
        try {
            $integrationId = (int)$this->getRequest()->getParam(self::PARAM_INTEGRATION_ID);
            if ($integrationId) {
                $integrationData = $this->_integrationService->get($integrationId);
                if (!$integrationData[Info::DATA_ID]) {
                    $this->_getSession()->addError(__('This integration no longer exists.'));
                    $this->_redirect('*/*/');
                    return;
                }
            }
            /** @var array $data */
            $data = $this->getRequest()->getPost();
            if (!empty($data)) {
                if (!isset($data['resource'])) {
                    $integrationData['resource'] = array();
                }
                $integrationData = array_merge($integrationData, $data);
                $this->_registry->register(self::REGISTRY_KEY_CURRENT_INTEGRATION, $integrationData);
                if (!isset($integrationData[Info::DATA_ID])) {
                    $this->_integrationService->create($integrationData);
                } else {
                    $this->_integrationService->update($integrationData);
                }
                $this->_getSession()
                    ->addSuccess(__('The integration \'%1\' has been saved.', $integrationData[Info::DATA_NAME]));
            } else {
                $this->_getSession()->addError(__('The integration was not saved.'));
            }
            $this->_redirect('*/*/');
        } catch (\Magento\Integration\Exception $e) {
            $this->_getSession()->addError($e->getMessage())->setIntegrationData($integrationData);
            $this->_redirectOnSaveError();
        } catch (\Magento\Core\Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirectOnSaveError();
        } catch (\Exception $e) {
            $this->_logger->logException($e);
            $this->_getSession()->addError($e->getMessage());
            $this->_redirectOnSaveError();
        }
    }

    /**
     * Activates the integration. Also contains intermediate steps (permissions confirmation and tokens).
     */
    public function activateAction()
    {
        $integrationId = (int)$this->getRequest()->getParam(self::PARAM_INTEGRATION_ID);

        if ($integrationId) {
            $integrationData = $this->_integrationService->get($integrationId);
            if (!$integrationData[Info::DATA_ID]) {
                $this->_getSession()->addError(__('This integration no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
            $this->_registry->register(self::REGISTRY_KEY_CURRENT_INTEGRATION, $integrationData);
        } else {
            $this->_getSession()->addError(__('Integration ID is not specified or is invalid.'));
            $this->_redirect('*/*/');
            return;
        }

        $dialogName = $this->getRequest()->getParam(self::PARAM_DIALOG_ID);

        if (in_array($dialogName, [self::DIALOG_PERMISSIONS, self::DIALOG_TOKENS])) {
            $this->_view->loadLayout($this->_getPopupHandleNames($dialogName));
        } else {
            $this->_view->loadLayout();
        }

        $this->_view->renderLayout();
    }

    /**
     * @param string $dialogName
     * @return array
     */
    protected function _getPopupHandleNames($dialogName)
    {
        $handles = [sprintf('%s_%s_popup', $this->_view->getDefaultLayoutHandle(), $dialogName)];

        if ($dialogName === self::DIALOG_PERMISSIONS) {
            $handleNodes = $this->_view->getLayout()->getUpdate()->getFileLayoutUpdatesXml()
                ->xpath('//referenceBlock[@name="integration.activate.permissions.tabs"]/../@id');

            if (is_array($handleNodes)) {
                foreach ($handleNodes as $node) {
                    $handles[] = (string)$node;
                }
            }
        }

        return $handles;
    }

    /**
     * Redirect merchant to 'Edit integration' or 'New integration' if error happened during integration save.
     */
    protected function _redirectOnSaveError()
    {
        $integrationId = $this->getRequest()->getParam(self::PARAM_INTEGRATION_ID);
        if ($integrationId) {
            $this->_redirect('*/*/edit', array('id' => $integrationId));
        } else {
            $this->_redirect('*/*/new');
        }
    }
}
