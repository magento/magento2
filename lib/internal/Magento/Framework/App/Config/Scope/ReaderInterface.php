<?php
/**
 * Scope Reader
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config\Scope;

interface ReaderInterface
{
    /**
     * Read configuration scope
     *
     * @return array
     */
    public function read();
}
