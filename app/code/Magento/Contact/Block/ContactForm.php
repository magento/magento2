<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Contact\Block;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Element\Template;

/**
 * Main contact form block
 *
 * @api
 * @since 100.0.2
 */
class ContactForm extends Template
{
    /**
     * @param Template\Context $context
     * @param array $data
     * @param ArgumentInterface|null $viewModel
     */
    public function __construct(Template\Context $context, array $data = [], ArgumentInterface $viewModel = null)
    {
        $data['view_model'] = $viewModel;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Returns action url for contact form
     *
     * @return string
     */
    public function getFormAction()
    {
        return $this->getUrl('contact/index/post', ['_secure' => true]);
    }
}
