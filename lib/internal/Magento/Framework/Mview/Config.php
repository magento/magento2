<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview;

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
     * Get views list
     *
     * @return array[]
     */
    public function getViews()
    {
        return $this->configData->get();
    }

    /**
     * Get view by ID
     *
     * @param string $viewId
     * @return array
     */
    public function getView($viewId)
    {
        return $this->configData->get($viewId);
    }
}
