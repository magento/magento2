<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Form;

/**
 * @api
 * @since 2.0.0
 */
class FormKey
{
    /**
     * Form key
     */
    const FORM_KEY = '_form_key';

    /**
     * @var \Magento\Framework\Math\Random
     * @since 2.0.0
     */
    protected $mathRandom;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     * @since 2.0.0
     */
    protected $session;

    /**
     * @var \Magento\Framework\Escaper
     * @since 2.1.0
     */
    protected $escaper;

    /**
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\Escaper $escaper
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->mathRandom = $mathRandom;
        $this->session = $session;
        $this->escaper = $escaper;
    }

    /**
     * Retrieve Session Form Key
     *
     * @return string A 16 bit unique key for forms
     * @since 2.0.0
     */
    public function getFormKey()
    {
        if (!$this->isPresent()) {
            $this->set($this->mathRandom->getRandomString(16));
        }
        return $this->escaper->escapeHtmlAttr($this->session->getData(self::FORM_KEY));
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    public function isPresent()
    {
        return (bool)$this->session->getData(self::FORM_KEY);
    }

    /**
     * @param string $value
     * @return void
     * @since 2.0.0
     */
    public function set($value)
    {
        $this->session->setData(self::FORM_KEY, $value);
    }
}
