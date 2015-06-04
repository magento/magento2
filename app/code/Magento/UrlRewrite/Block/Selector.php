<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Block;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Template;
use Magento\UrlRewrite\Model\Modes;

class Selector extends Template
{
    /**
     * List of available modes from source model
     * key => label
     *
     * @var array
     */
    protected $_modes;

    protected $modesInstance;

    /**
     * @var string
     */
    protected $_template = 'selector.phtml';

    public function __construct(
        Modes $modes,
        Context $context,
        array $data = []
    )
    {
        $this->modesInstance = $modes;
        parent::__construct($context, $data);
        $this->_modes = $this->modesInstance->toOptionsArray();
    }

    /**
     * Available modes getter
     *
     * @return array
     */
    public function getModes()
    {
        return $this->_modes;
    }

    /**
     * Label getter
     *
     * @return \Magento\Framework\Phrase
     */
    public function getSelectorLabel()
    {
        return __('Create URL Rewrite:');
    }

    /**
     * Check whether selection is in specified mode
     *
     * @param string $mode
     * @return bool
     */
    public function isMode($mode)
    {
        return $this->getRequest()->has($mode);
    }

    /**
     * Get default mode
     *
     * @return string
     */
    public function getDefaultMode()
    {
        $keys = array_keys($this->_modes);
        return array_shift($keys);
    }
}
