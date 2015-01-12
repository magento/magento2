<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Model\Observer;

class RegisterFormKeyFromCookie
{
    /**
     * @var \Magento\Framework\App\PageCache\FormKey
     */
    protected $_formKey;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $_session;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * @param \Magento\Framework\Session\Generic $session
     * @param \Magento\Framework\App\PageCache\FormKey $formKey
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        \Magento\Framework\App\PageCache\FormKey $formKey,
        \Magento\Framework\Session\Generic $session,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->_session = $session;
        $this->_formKey = $formKey;
        $this->_escaper = $escaper;
    }

    /**
     * Register form key in session from cookie value
     *
     * @return void
     */
    public function execute()
    {
        $formKeyFromCookie = $this->_formKey->get();
        if ($formKeyFromCookie) {
            $this->_session->setData(
                \Magento\Framework\Data\Form\FormKey::FORM_KEY,
                $this->_escaper->escapeHtml($formKeyFromCookie)
            );
        }
    }
}
