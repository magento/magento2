<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Config;

class ViewFactory
{
    /**
     * @return View
     */
    public function create($configFiles)
    {
        return new \Magento\Framework\Config\View(
            $configFiles,
            new \Magento\Framework\Config\Dom\UrnResolver()
        );
    }
}
