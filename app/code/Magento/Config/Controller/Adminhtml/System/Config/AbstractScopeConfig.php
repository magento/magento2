<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Controller\Adminhtml\System\Config;

use Magento\Config\Controller\Adminhtml\System\ConfigSectionChecker;

/**
 * @api
 * @since 2.0.0
 */
abstract class AbstractScopeConfig extends \Magento\Config\Controller\Adminhtml\System\AbstractConfig
{
    /**
     * @var \Magento\Config\Model\Config
     * @since 2.0.0
     */
    protected $_backendConfig;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Config\Model\Config\Structure $configStructure
     * @param ConfigSectionChecker $sectionChecker
     * @param \Magento\Config\Model\Config $backendConfig
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Config\Model\Config\Structure $configStructure,
        ConfigSectionChecker $sectionChecker,
        \Magento\Config\Model\Config $backendConfig
    ) {
        $this->_backendConfig = $backendConfig;
        parent::__construct($context, $configStructure, $sectionChecker);
    }

    /**
     * Sets scope for backend config
     *
     * @param string $sectionId
     * @return bool
     * @since 2.0.0
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
