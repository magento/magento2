<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Form\FormKey;

use Magento\Framework\Encryption\Helper\Security;

/**
 * @api
 */
class Validator
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $_formKey;

    /**
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     */
    public function __construct(\Magento\Framework\Data\Form\FormKey $formKey)
    {
        $this->_formKey = $formKey;
    }

    /**
     * Validate form key
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function validate(\Magento\Framework\App\RequestInterface $request)
    {
        $formKey = $request->getParam('form_key', null);
        
        return $formKey && Security::compareStrings($formKey, $this->_formKey->getFormKey());
    }
}
