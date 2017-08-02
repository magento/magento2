<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Block\Page;

/**
 * Require Js block
 *
 * @api
 * @since 2.0.0
 */
class RequireJs extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     * @since 2.0.0
     */
    protected $formKey;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
        array $data = []
    ) {
        $this->formKey = $formKey;
        parent::__construct(
            $context,
            $data
        );
        $this->formKey = $formKey;
    }

    /**
     * Retrieve Session Form Key
     *
     * @return string
     * @since 2.0.0
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
}
