<?php
/**
 * Scope Reader
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
