<?php
/**
 * Application language config factory
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\App\Language;

/**
 * @codeCoverageIgnore
 */
class ConfigFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create config
     *
     * @param array $arguments
     * @return Config
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Magento\Framework\App\Language\Config', $arguments);
    }
}
