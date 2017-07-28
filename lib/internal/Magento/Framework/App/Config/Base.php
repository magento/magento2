<?php
/**
 * Abstract configuration class
 * Used to retrieve core configuration values
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

/**
 * Class \Magento\Framework\App\Config\Base
 *
 * @since 2.0.0
 */
class Base extends \Magento\Framework\Simplexml\Config
{
    /**
     * List of instances
     *
     * @var Base[]
     * @since 2.0.0
     */
    public static $instances = [];

    /**
     * @param \Magento\Framework\Simplexml\Element|string $sourceData $sourceData
     * @since 2.0.0
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
     * @since 2.0.0
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
