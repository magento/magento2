<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\Config\Reader;

use Magento\Framework\App\Config;

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
     * @var Config
     */
    private $config;

    /**
     * Initialize parameters
     *
     * @param Config $config
     * @param \Magento\Cron\Model\Config\Converter\Db $converter
     */
    public function __construct(
        Config $config,
        \Magento\Cron\Model\Config\Converter\Db $converter
    ) {
        $this->config = $config;
        $this->_converter = $converter;
    }

    /**
     * Return converted data
     *
     * @return array
     */
    public function get()
    {
        return $this->_converter->convert($this->config->get('system/default'));
    }
}
