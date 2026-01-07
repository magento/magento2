<?php
/**
 * Application default url
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
        return $this->_parts[$code] ?? null;
    }
}
