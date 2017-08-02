<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Frontend form key content block
 */
namespace Magento\Framework\View\Element;

/**
 * @api
 * @since 2.0.0
 */
class FormKey extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     * @since 2.0.0
     */
    protected $formKey;

    /**
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
        array $data = []
    ) {
        $this->formKey = $formKey;
        parent::__construct($context, $data);
    }

    /**
     * Get form key
     *
     * @return string
     * @since 2.0.0
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        return '<input name="form_key" type="hidden" value="' . $this->getFormKey() . '" />';
    }
}
