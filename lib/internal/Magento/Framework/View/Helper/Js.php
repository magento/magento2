<?php
/**
 * JavaScript helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
     * @return  string
     */
    public function getScript($script)
    {
        $scriptString = '//<![CDATA[' . "\n{$script}\n" . '//]]>';

        return /* @noEscape */ $this->secureRenderer->renderTag('script', [], $scriptString, false);
    }
}
