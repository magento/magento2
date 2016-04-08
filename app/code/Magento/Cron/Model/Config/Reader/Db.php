<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\Config\Reader;

/**
 * Reader for cron parameters from data base storage
 */
class Db
{
    /**
     * Converter instance
     *
     * @var \Magento\Cron\Model\Config\Converter\Db
     */
    protected $_converter;

    /**
     * @var \Magento\Framework\App\Config\Scope\ReaderInterface
     */
    protected $_reader;

    /**
     * Initialize parameters
     *
     * @param \Magento\Framework\App\Config\Scope\ReaderInterface $defaultReader
     * @param \Magento\Cron\Model\Config\Converter\Db $converter
     */
    public function __construct(
        \Magento\Framework\App\Config\Scope\ReaderInterface $defaultReader,
        \Magento\Cron\Model\Config\Converter\Db $converter
    ) {
        $this->_reader = $defaultReader;
        $this->_converter = $converter;
    }

    /**
     * Return converted data
     *
     * @return array
     */
    public function get()
    {
        return $this->_converter->convert($this->_reader->read());
    }
}
