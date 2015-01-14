<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Controller\Adminhtml\System\Config;

use Magento\Backend\Controller\Adminhtml\System\ConfigSectionChecker;

abstract class AbstractScopeConfig extends \Magento\Backend\Controller\Adminhtml\System\AbstractConfig
{
    /**
     * @var \Magento\Backend\Model\Config
     */
    protected $_backendConfig;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\Config\Structure $configStructure
     * @param ConfigSectionChecker $sectionChecker
     * @param \Magento\Backend\Model\Config $backendConfig
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\Config\Structure $configStructure,
        ConfigSectionChecker $sectionChecker,
        \Magento\Backend\Model\Config $backendConfig
    ) {
        $this->_backendConfig = $backendConfig;
        parent::__construct($context, $configStructure, $sectionChecker);
    }

    /**
     * Sets scope for backend config
     *
     * @param string $sectionId
     * @return bool
     */
    protected function isSectionAllowed($sectionId)
    {
        $website = $this->getRequest()->getParam('website');
        $store = $this->getRequest()->getParam('store');
        if ($store) {
            $this->_backendConfig->setStore($store);
        } elseif ($website) {
            $this->_backendConfig->setWebsite($website);
        }
        return parent::isSectionAllowed($sectionId);
    }
}
