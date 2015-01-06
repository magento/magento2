<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Store\Model\Config\Reader;

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
