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
     * Retrieve framed javascript
     *
     * @param   string $script
     * @return  string
     */
    public function getScript($script)
    {
        $secureHtmlRenderer = ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
        $scriptString = '//<![CDATA[' . "\n{$script}\n" . '//]]>';

        return /* @noEscape */ $secureHtmlRenderer->renderTag('script', [], $scriptString, false);
    }
}
