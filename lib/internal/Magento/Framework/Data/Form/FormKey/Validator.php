<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Form\FormKey;

/**
 * @api
 * @since 2.0.0
 */
class Validator
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     * @since 2.0.0
     */
    protected $_formKey;

    /**
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @since 2.0.0
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
     * @since 2.0.0
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
