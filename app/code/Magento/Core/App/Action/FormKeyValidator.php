<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\App\Action;

class FormKeyValidator
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
        if (!$formKey || $formKey !== $this->_formKey->getFormKey()) {
            return false;
        }
        return true;
    }
}
