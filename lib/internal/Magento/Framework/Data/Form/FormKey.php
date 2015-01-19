<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     */
    public function __construct(
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Framework\Session\SessionManagerInterface $session
    ) {
        $this->mathRandom = $mathRandom;
        $this->session = $session;
    }

    /**
     * Retrieve Session Form Key
     *
     * @return string A 16 bit unique key for forms
     */
    public function getFormKey()
    {
        if (!$this->session->getData(self::FORM_KEY)) {
            $this->session->setData(self::FORM_KEY, $this->mathRandom->getRandomString(16));
        }
        return $this->session->getData(self::FORM_KEY);
    }
}
