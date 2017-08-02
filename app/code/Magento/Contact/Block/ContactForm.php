<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Contact\Block;

use Magento\Framework\View\Element\Template;

/**
 * Main contact form block
 *
 * @api
 * @since 2.0.0
 */
class ContactForm extends Template
{
    /**
     * @param Template\Context $context
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(Template\Context $context, array $data = [])
    {
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Returns action url for contact form
     *
     * @return string
     * @since 2.0.0
     */
    public function getFormAction()
    {
        return $this->getUrl('contact/index/post', ['_secure' => true]);
    }
}
