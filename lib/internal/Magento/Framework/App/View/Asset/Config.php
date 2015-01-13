<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\App\View\Asset;

class Config implements ConfigInterface
{
    /**
     * @var Config\Data
     */
    protected $configData;

    /**
     * @param Config\Data $configData
     */
    public function __construct(Config\Data $configData)
    {
        $this->configData = $configData;
    }

    /**
     * Get exclude list
     *
     * @param string $area
     * @return array
     */
    public function getExcludedFiles($area)
    {
        return $this->configData->get("file/$area");
    }

    /**
     * Get exclude directory
     *
     * @param string $area
     * @return array
     */
    public function getExcludedDir($area)
    {
        return $this->configData->get("directory/$area");
    }
}
