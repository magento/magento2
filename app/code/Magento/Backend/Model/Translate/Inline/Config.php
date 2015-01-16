<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Translate\Inline;

/**
 * Backend Inline Translation config
 */
class Config implements \Magento\Framework\Translate\Inline\ConfigInterface
{
    /**
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $_config;

    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_helper;

    /**
     * @param \Magento\Backend\App\ConfigInterface $config
     * @param \Magento\Core\Helper\Data $helper
     */
    public function __construct(\Magento\Backend\App\ConfigInterface $config, \Magento\Core\Helper\Data $helper)
    {
        $this->_config = $config;
        $this->_helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function isActive($scope = null)
    {
        return $this->_config->isSetFlag('dev/translate_inline/active_admin');
    }

    /**
     * {@inheritdoc}
     */
    public function isDevAllowed($scope = null)
    {
        return $this->_helper->isDevAllowed($scope);
    }
}
