<?php
/**
 * Abstract application router
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

interface DefaultPathInterface
{
    /**
     * @param string $code
     * @return string
     */
    public function getPart($code);
}
