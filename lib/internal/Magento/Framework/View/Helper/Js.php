<?php

/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\View\Helper;

use Magento\Framework\App\ObjectManager;

/**
 * Class Js help render script.
 */
class Js
{
    /**
     * @var SecureHtmlRenderer
     */
    protected $secureRenderer;

    /**
     * @param SecureHtmlRenderer $htmlRenderer
     */
    public function __construct(
        SecureHtmlRenderer $htmlRenderer
    ) {
        $this->secureRenderer = $htmlRenderer;
    }

    /**
     * Retrieve framed javascript
     *
     * @param   string $script
     *
     * @return  string
     */
    public function getScript($script)
    {
        $scriptString = '//<![CDATA[' . "\n{$script}\n" . '//]]>';

        return /* @noEscape */ $this->secureRenderer->renderTag('script', [], $scriptString, false);
    }
}
