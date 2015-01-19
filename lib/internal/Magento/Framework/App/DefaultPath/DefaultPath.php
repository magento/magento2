<?php
/**
 * Application default url
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\DefaultPath;

class DefaultPath implements \Magento\Framework\App\DefaultPathInterface
{
    /**
     * Default path parts
     *
     * @var array
     */
    protected $_parts;

    /**
     * @param array $parts
     */
    public function __construct(array $parts)
    {
        $this->_parts = $parts;
    }

    /**
     * Retrieve path part by key
     *
     * @param string $code
     * @return string
     */
    public function getPart($code)
    {
        return isset($this->_parts[$code]) ? $this->_parts[$code] : null;
    }
}
