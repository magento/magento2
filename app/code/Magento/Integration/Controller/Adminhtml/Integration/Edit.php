<?php
/**
 *
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Integration\Controller\Adminhtml\Integration;

use \Magento\Backend\App\Action;
use \Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info;
use \Magento\Integration\Exception as IntegrationException;

class Edit extends \Magento\Integration\Controller\Adminhtml\Integration
{
    /**
     * Edit integration action.
     *
     * @return void
     */
    public function execute()
    {
        /** Try to recover integration data from session if it was added during previous request which failed. */
        $integrationId = (int)$this->getRequest()->getParam(self::PARAM_INTEGRATION_ID);
        if ($integrationId) {
            try {
                $integrationData = $this->_integrationService->get($integrationId)->getData();
                $originalName = $this->escaper->escapeHtml($integrationData[Info::DATA_NAME]);
            } catch (IntegrationException $e) {
                $this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $this->_logger->logException($e);
                $this->messageManager->addError(__('Internal error. Check exception log for details.'));
                $this->_redirect('*/*');
                return;
            }
            $restoredIntegration = $this->_getSession()->getIntegrationData();
            if (isset($restoredIntegration[Info::DATA_ID]) && $integrationId == $restoredIntegration[Info::DATA_ID]) {
                $integrationData = array_merge($integrationData, $restoredIntegration);
            }
        } else {
            $this->messageManager->addError(__('Integration ID is not specified or is invalid.'));
            $this->_redirect('*/*/');
            return;
        }
        $this->_registry->register(self::REGISTRY_KEY_CURRENT_INTEGRATION, $integrationData);
        $this->_view->loadLayout();
        $this->_getSession()->setIntegrationData(array());
        $this->_setActiveMenu('Magento_Integration::system_integrations');

        if ($this->_integrationData->isConfigType($integrationData)) {
            $title = __('View "%1" Integration', $originalName);
        } else {
            $title = __('Edit "%1" Integration', $originalName);
        }

        $this->_addBreadcrumb($title, $title);
        $this->_title->add($title);
        $this->_view->renderLayout();
    }
}
