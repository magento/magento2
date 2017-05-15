<?php
/**
 * Abstract configuration class
 * Used to retrieve core configuration values
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

class Base extends \Magento\Framework\Simplexml\Config
{
    /**
     * List of instances
     *
     * @var Base[]
     */
    public static $instances = [];

    /**
     * @param string|\Magento\Framework\Simplexml\Element $sourceData $sourceData
     */
    public function __construct($sourceData = null)
    {
        $this->_elementClass = \Magento\Framework\App\Config\Element::class;
        parent::__construct($sourceData);
        self::$instances[] = $this;
    }

    /**
     * Cleanup objects because of simplexml memory leak
     *
     * @return void
     */
    public static function destroy()
    {
        if (is_array(self::$instances)) {
            foreach (self::$instances as $instance) {
                $instance->_xml = null;
            }
        }
        self::$instances = [];
    }
}
