<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Form;

class FormKey
{
    /**
     * Form key
     */
    const FORM_KEY = '_form_key';

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $session;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\Escaper $escaper
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
     */
    public function isPresent()
    {
        return (bool)$this->session->getData(self::FORM_KEY);
    }

    /**
     * @param string $value
     * @return void
     */
    public function set($value)
    {
        $this->session->setData(self::FORM_KEY, $value);
    }
}
