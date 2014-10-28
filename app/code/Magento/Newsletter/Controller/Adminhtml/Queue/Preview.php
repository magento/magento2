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
namespace Magento\Newsletter\Controller\Adminhtml\Queue;

class Preview extends \Magento\Newsletter\Controller\Adminhtml\Queue
{
    /**
     * Preview Newsletter queue template
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $data = $this->getRequest()->getParams();
        if (empty($data) || !isset($data['id'])) {
            $this->_forward('noroute');
            return;
        }

        // set default value for selected store
        $data['preview_store_id'] = $this->_objectManager->get(
            'Magento\Store\Model\StoreManager'
        )->getDefaultStoreView()->getId();

        $this->_view->getLayout()->getBlock('preview_form')->setFormData($data);
        $this->_view->renderLayout();
    }
}
