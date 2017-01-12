<?php
/**
 * JavaScript helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Helper;

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
        return '<script type="text/javascript">//<![CDATA[' . "\n{$script}\n" . '//]]></script>';
    }
}
