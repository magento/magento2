<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Controller\Adminhtml\System;

use Magento\Config\Controller\Adminhtml\System\ConfigSectionChecker;

/**
 * System Configuration Abstract Controller
 */
abstract class AbstractConfig extends \Magento\Backend\App\AbstractAction
{
    /**
     * @var \Magento\Config\Model\Config\Structure
     */
    protected $_configStructure;

    /**
     * @var ConfigSectionChecker
     */
    protected $_sectionChecker;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Config\Model\Config\Structure $configStructure
     * @param ConfigSectionChecker $sectionChecker
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Config\Model\Config\Structure $configStructure,
        ConfigSectionChecker $sectionChecker
    ) {
        parent::__construct($context);
        $this->_configStructure = $configStructure;
        $this->_sectionChecker = $sectionChecker;
    }

    /**
     * Check if current section is found and is allowed
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$request->getParam('section')) {
            $request->setParam('section', $this->_configStructure->getFirstSection()->getId());
        }
        return parent::dispatch($request);
    }

    /**
     * Check is allow modify system configuration
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        $sectionId = $this->_request->getParam('section');
        return $this->_authorization->isAllowed('Magento_Config::config')
            || $this->_configStructure->getElement($sectionId)->isAllowed();
    }

    /**
     * Save state of configuration field sets
     *
     * @param array $configState
     * @return bool
     */
    protected function _saveState($configState = [])
    {
        $adminUser = $this->_auth->getUser();
        if (is_array($configState)) {
            $extra = $adminUser->getExtra();
            if (!is_array($extra)) {
                $extra = [];
            }
            if (!isset($extra['configState'])) {
                $extra['configState'] = [];
            }
            foreach ($configState as $fieldset => $state) {
                $extra['configState'][$fieldset] = $state;
            }
            $adminUser->saveExtra($extra);
        }
        return true;
    }
}
