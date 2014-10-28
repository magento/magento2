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
namespace Magento\Backend\Controller\Adminhtml\System\Design;

class Edit extends \Magento\Backend\Controller\Adminhtml\System\Design
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_title->add(__('Store Design'));

        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Backend::system_design_schedule');

        $id = (int)$this->getRequest()->getParam('id');
        $design = $this->_objectManager->create('Magento\Framework\App\DesignInterface');

        if ($id) {
            $design->load($id);
        }

        $this->_title->add($design->getId() ? __('Edit Store Design Change') : __('New Store Design Change'));

        $this->_coreRegistry->register('design', $design);

        $this->_addContent($this->_view->getLayout()->createBlock('Magento\Backend\Block\System\Design\Edit'));
        $this->_addLeft(
            $this->_view->getLayout()->createBlock('Magento\Backend\Block\System\Design\Edit\Tabs', 'design_tabs')
        );

        $this->_view->renderLayout();
    }
}
