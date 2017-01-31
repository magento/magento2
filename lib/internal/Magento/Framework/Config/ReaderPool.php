<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

class ReaderPool implements \Magento\Framework\App\Config\Scope\ReaderPoolInterface
{
    /**
     * List of readers
     *
     * @var array
     */
    protected $_readers = [];

    /**
     * @param \Magento\Framework\App\Config\Scope\ReaderInterface[] $readers
     */
    public function __construct(
        array $readers
    ) {
        $this->_readers = $readers;
    }

    /**
     * Retrieve reader by scope type
     *
     * @param string $scopeType
     * @return mixed
     */
    public function getReader($scopeType)
    {
        return $this->_readers[$scopeType];
    }
}
