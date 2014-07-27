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
namespace Magento\Install\Controller\Wizard;

use \Magento\Framework\App\ResponseInterface;

class ConfigPost extends \Magento\Install\Controller\Wizard
{
    /**
     * Process configuration POST data
     *
     * @return ResponseInterface|void
     */
    public function execute()
    {
        $this->_checkIfInstalled();
        $step = $this->_getWizard()->getStepByName('config');

        $config = $this->getRequest()->getPost('config');
        $connectionConfig = $this->getRequest()->getPost('connection');

        if ($config && $connectionConfig && isset($connectionConfig[$config['db_model']])) {

            $data = array_merge($config, $connectionConfig[$config['db_model']]);

            $this->_session->setConfigData(
                $data
            )->setSkipUrlValidation(
                $this->getRequest()->getPost('skip_url_validation')
            )->setSkipBaseUrlValidation(
                $this->getRequest()->getPost('skip_base_url_validation')
            );
            try {
                $this->_getInstaller()->installConfig($data);
                return $this->_redirect('*/*/installDb');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->getResponse()->setRedirect($step->getUrl());
            }
        }
        $this->getResponse()->setRedirect($step->getUrl());
    }
}
