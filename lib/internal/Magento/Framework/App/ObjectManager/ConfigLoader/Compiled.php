<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\App\ObjectManager\ConfigLoader;

use Magento\Framework\App\Area;

class Compiled extends \Magento\Framework\App\ObjectManager\ConfigLoader
{
    /**
     * Global config
     *
     * @var array
     */
    private $globalConfig = [];

    /**
     * Compiled construct
     *
     * @param array $globalConfig
     */
    public function __construct(array $globalConfig = [])
    {
        $this->globalConfig = $globalConfig;
    }

    /**
     * Load modules DI configuration
     *
     * @param string $area
     * @return array|mixed
     */
    public function load($area)
    {
        if ($area == Area::AREA_GLOBAL) {
            return $this->globalConfig;
        }
        return \unserialize(\file_get_contents(BP . '/var/di/' . $area . '.ser'));
    }
}
