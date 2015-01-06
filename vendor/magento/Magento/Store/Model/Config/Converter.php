<?php
/**
 * DB configuration data converter. Converts associative array to tree array
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Store\Model\Config;

class Converter extends \Magento\Framework\App\Config\Scope\Converter
{
    /**
     * @var \Magento\Store\Model\Config\Processor\Placeholder
     */
    protected $_processor;

    /**
     * @param \Magento\Store\Model\Config\Processor\Placeholder $processor
     */
    public function __construct(\Magento\Store\Model\Config\Processor\Placeholder $processor)
    {
        $this->_processor = $processor;
    }

    /**
     * Convert config data
     *
     * @param array $source
     * @param array $initialConfig
     * @return array
     */
    public function convert($source, $initialConfig = [])
    {
        $config = array_replace_recursive($initialConfig, parent::convert($source));
        return $this->_processor->process($config);
    }
}
