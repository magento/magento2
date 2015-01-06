<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Centinel\Controller\Adminhtml\Centinel\Index;

class AuthenticationStart extends \Magento\Centinel\Controller\Adminhtml\Centinel\Index
{
    /**
     * Process autentication start action
     *
     * @return void
     */
    public function execute()
    {
        $validator = $this->_getValidator();
        if ($validator) {
            $this->_coreRegistry->register('current_centinel_validator', $validator);
        }
        $this->_view->loadLayout()->renderLayout();
    }
}
