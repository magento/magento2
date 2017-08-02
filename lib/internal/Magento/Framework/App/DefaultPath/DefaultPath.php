<?php
/**
 * Application default url
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\DefaultPath;

/**
 * Class \Magento\Framework\App\DefaultPath\DefaultPath
 *
 * @since 2.0.0
 */
class DefaultPath implements \Magento\Framework\App\DefaultPathInterface
{
    /**
     * Default path parts
     *
     * @var array
     * @since 2.0.0
     */
    protected $_parts;

    /**
     * @param array $parts
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getPart($code)
    {
        return isset($this->_parts[$code]) ? $this->_parts[$code] : null;
    }
}
