<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element;

/**
 * Customer Redirect Page
 * @since 2.0.0
 */
class Redirect extends Template
{
    /**
     *  HTML form hidden fields
     *
     * @var array
     * @since 2.0.0
     */
    protected $formFields = [];

    /**
     * Form factory
     *
     * @var \Magento\Framework\Data\FormFactory
     * @since 2.0.0
     */
    protected $formFactory;

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        parent::__construct($context, $data);
    }

    /**
     * URL for redirect location
     *
     * @return string URL
     * @since 2.0.0
     */
    public function getTargetURL()
    {
        return '';
    }

    /**
     * Additional custom message
     *
     * @return string Output message
     * @since 2.0.0
     */
    public function getMessage()
    {
        return '';
    }

    /**
     * Client-side redirect engine output
     *
     * @return string
     * @since 2.0.0
     */
    public function getRedirectOutput()
    {
        if ($this->isHtmlFormRedirect()) {
            return $this->getHtmlFormRedirect();
        } else {
            return $this->getRedirect();
        }
    }

    /**
     * Redirect via JS location
     *
     * @return string
     * @since 2.0.0
     */
    public function getRedirect()
    {
        return '<script type="text/javascript">
            (function($){
                $($.mage.redirect("' .
            $this->getTargetURL() .
            '"));
            })(jQuery);
        </script>';
    }

    /**
     * Redirect via HTML form submission
     *
     * @return string
     * @since 2.0.0
     */
    public function getHtmlFormRedirect()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->formFactory->create();
        $form->setAction(
            $this->getTargetURL()
        )->setId(
            $this->getFormId()
        )->setName(
            $this->getFormId()
        )->setAttr(
            'data-auto-submit',
            'true'
        )->setMethod(
            $this->getFormMethod()
        )->setUseContainer(
            true
        );
        foreach ($this->_getFormFields() as $field => $value) {
            $form->addField($field, 'hidden', ['name' => $field, 'value' => $value]);
        }
        return $form->toHtml();
    }

    /**
     * HTML form or JS redirect
     *
     * @return bool
     * @since 2.0.0
     */
    public function isHtmlFormRedirect()
    {
        return is_array($this->_getFormFields()) && count($this->_getFormFields()) > 0;
    }

    /**
     * HTML form id/name attributes
     *
     * @return string Id/name
     * @since 2.0.0
     */
    public function getFormId()
    {
        return '';
    }

    /**
     * HTML form method attribute
     *
     * @return string Method
     * @since 2.0.0
     */
    public function getFormMethod()
    {
        return 'POST';
    }

    /**
     * Array of hidden form fields (name => value)
     *
     * @return array
     * @since 2.0.0
     */
    public function getFormFields()
    {
        return [];
    }

    /**
     * Optimized getFormFields() method
     *
     * @return array
     * @since 2.0.0
     */
    protected function _getFormFields()
    {
        if (!is_array($this->formFields) || count($this->formFields) == 0) {
            $this->formFields = $this->getFormFields();
        }
        return $this->formFields;
    }

    /**
     * Get default HTML
     *
     * @return string
     * @since 2.0.0
     */
    protected function _getDefaultHtml()
    {
        $html = '';

        $html .= '<div class="page-title-wrapper">';
        $html .= '<h1>' . (string)new \Magento\Framework\Phrase('Redirecting...') . '</h1>';
        $html .= '</div>';
        if ($this->getMessage()) {
            $html .= '<p>' . $this->getMessage() . '</p>';
        }
        $html .= $this->getRedirectOutput();
        if (!$this->isHtmlFormRedirect()) {
            $html .= '<p>'
                . (string)new \Magento\Framework\Phrase(
                    'Click <a href="%1">here</a> if nothing has happened',
                    [$this->getTargetURL()]
                )
                . '</p>';
        }

        return $html;
    }

    /**
     * Render block HTML
     *
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        if ($this->getTemplate()) {
            $html = parent::_toHtml();
        } else {
            $html = $this->_getDefaultHtml();
        }
        return $html;
    }
}
