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
namespace Magento\Backend\Controller\Adminhtml\System\Config;

class State extends AbstractScopeConfig
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\Config\Structure $configStructure
     * @param \Magento\Backend\Controller\Adminhtml\System\ConfigSectionChecker $sectionChecker
     * @param \Magento\Backend\Model\Config $backendConfig
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRawFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\Config\Structure $configStructure,
        \Magento\Backend\Controller\Adminhtml\System\ConfigSectionChecker $sectionChecker,
        \Magento\Backend\Model\Config $backendConfig,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRawFactory
    ) {
        parent::__construct($context, $configStructure, $sectionChecker, $backendConfig);
        $this->resultRawFactory = $resultRawFactory;
    }

    /**
     * Save fieldset state through AJAX
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        if ($this->getRequest()->getParam('isAjax')
            && $this->getRequest()->getParam('container')!= ''
            && $this->getRequest()->getParam('value') != ''
        ) {
            $configState = array($this->getRequest()->getParam('container') => $this->getRequest()->getParam('value'));
            $this->_saveState($configState);
            /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
            $resultRaw = $this->resultRawFactory->create();
            return $resultRaw->setContents('success');
        }
    }
}
