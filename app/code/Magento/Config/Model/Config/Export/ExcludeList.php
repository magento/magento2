<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Export;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class ExcludeList contains list of config fields which should be excluded from config export file
 */
class ExcludeList
{
    /**
     * @var array
     */
    private $configs;

    /**
     * @param array $configs
     */
    public function __construct(array $configs = [])
    {
        $this->configs = $configs;
    }

    /**
     * Check whether config item is excluded from export
     *
     * @param string $path
     * @return bool
     */
    public function isPresent($path)
    {
        return !empty($this->configs[$path]) ;
    }

    /**
     * Retrieves all excluded field paths for export
     *
     * @return array
     */
    public function get()
    {
        return array_keys(
            array_filter(
                $this->configs,
                function ($value) {
                    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                }
            )
        );
    }
}
