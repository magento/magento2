<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
