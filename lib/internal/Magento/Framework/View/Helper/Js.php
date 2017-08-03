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

/**
 * Class \Magento\Framework\View\Helper\Js
 *
 * @since 2.0.0
 */
class Js
{
    /**
     * Retrieve framed javascript
     *
     * @param   string $script
     * @return  string
     * @since 2.0.0
     */
    public function getScript($script)
    {
        return '<script type="text/javascript">//<![CDATA[' . "\n{$script}\n" . '//]]></script>';
    }
}
