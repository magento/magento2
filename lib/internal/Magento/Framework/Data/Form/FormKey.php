<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Form;

/**
 * Class FormKey
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @since 100.0.2
 */
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
     * @since 100.0.3
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFormKey()
    {
        if (!$this->isPresent()) {
            $this->set($this->mathRandom->getRandomString(16));
        }
        return $this->escaper->escapeJs($this->session->getData(self::FORM_KEY));
    }

    /**
     * Determine if the form key is present in the session
     *
     * @return bool
     */
    public function isPresent()
    {
        return (bool) $this->session->getData(self::FORM_KEY);
    }

    /**
     * Set the value of the form key
     *
     * @param string $value
     * @return void
     */
    public function set($value)
    {
        $this->session->setData(self::FORM_KEY, $value);
    }
}
