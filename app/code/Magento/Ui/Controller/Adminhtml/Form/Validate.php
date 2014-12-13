<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Ui\Controller\Adminhtml\Form;

class Validate extends \Magento\Ui\Controller\Adminhtml\AbstractAction
{
    /**
     * Action for AJAX request
     *
     * @return void
     */
    public function execute()
    {
        $component = $this->getComponent();
        $name = $this->getName();
        if ($component && $name) {
            $formElement = $this->factory->createUiComponent($component, $name);
            list($module, $controller, $action) = explode('\\', $formElement->getValidateMca());
            $this->_forward($action, $controller, $module, $this->getRequest()->getParams());
        } else {
            $this->_redirect('admin');
        }
    }
}
