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
 * @package     Magento_Backend
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Controller\Adminhtml\System;

use Magento\App\ResponseInterface;

/**
 * System Configuration controller
 */
class Config extends AbstractConfig
{
    /**
     * @var \Magento\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\Config\Structure $configStructure
     * @param \Magento\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\Config\Structure $configStructure,
        \Magento\App\Response\Http\FileFactory $fileFactory,
        \Magento\Core\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        $this->_fileFactory = $fileFactory;
        parent::__construct($context, $configStructure);
    }

    /**
     * Index action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_forward('edit');
    }

    /**
     * Edit configuration section
     *
     * @return \Magento\App\ResponseInterface|void
     */
    public function editAction()
    {
        $this->_title->add(__('Configuration'));

        $current = $this->getRequest()->getParam('section');
        $website = $this->getRequest()->getParam('website');
        $store = $this->getRequest()->getParam('store');

        /** @var $section \Magento\Backend\Model\Config\Structure\Element\Section */
        $section = $this->_configStructure->getElement($current);
        if ($current && !$section->isVisible($website, $store)) {
            return $this->_redirect('adminhtml/*/', array('website' => $website, 'store' => $store));
        }

        $this->_view->loadLayout();

        $this->_setActiveMenu('Magento_Backend::system_config');
        $this->_view->getLayout()->getBlock('menu')->setAdditionalCacheKeyInfo(array($current));

        $this->_addBreadcrumb(__('System'), __('System'), $this->getUrl('*\/system'));

        $this->_view->renderLayout();
    }

    /**
     * Save fieldset state through AJAX
     *
     * @return void
     */
    public function stateAction()
    {
        if ($this->getRequest()->getParam(
            'isAjax'
        ) && $this->getRequest()->getParam(
            'container'
        ) != '' && $this->getRequest()->getParam(
            'value'
        ) != ''
        ) {
            $configState = array($this->getRequest()->getParam('container') => $this->getRequest()->getParam('value'));
            $this->_saveState($configState);
            $this->getResponse()->setBody('success');
        }
    }
}
