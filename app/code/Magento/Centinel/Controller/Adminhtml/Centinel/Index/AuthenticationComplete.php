<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Centinel\Controller\Adminhtml\Centinel\Index;

class AuthenticationComplete extends \Magento\Centinel\Controller\Adminhtml\Centinel\Index
{
    /**
     * Process autentication complete action
     *
     * @return void
     */
    public function execute()
    {
        try {
            $validator = $this->_getValidator();
            if ($validator) {
                $request = $this->getRequest();

                $data = new \Magento\Framework\Object();
                $data->setTransactionId($request->getParam('MD'));
                $data->setPaResPayload($request->getParam('PaRes'));

                $validator->authenticate($data);
                $this->_coreRegistry->register('current_centinel_validator', $validator);
            }
        } catch (\Exception $e) {
            $this->_coreRegistry->register('current_centinel_validator', false);
        }
        $this->_view->loadLayout()->renderLayout();
    }
}
