<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\Config\Reader;

use Magento\Framework\App\Config;

/**
 * Reader for cron parameters from data base storage
 * @since 2.0.0
 */
class Db
{
    /**
     * Converter instance
     *
     * @var \Magento\Cron\Model\Config\Converter\Db
     * @since 2.0.0
     */
    protected $_converter;

    /**
     * @var \Magento\Framework\App\Config\Scope\ReaderInterface
     * @since 2.0.0
     */
    protected $_reader;

    /**
     * @var Config
     * @since 2.2.0
     */
    private $config;

    /**
     * Initialize parameters
     *
     * @param Config $config
     * @param \Magento\Cron\Model\Config\Converter\Db $converter
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function get()
    {
        return $this->_converter->convert($this->config->get('system', 'default'));
    }
}
