<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview;

/**
 * Class \Magento\Framework\Mview\Config
 *
 * @since 2.0.0
 */
class Config implements ConfigInterface
{
    /**
     * @var Config\Data
     * @since 2.0.0
     */
    protected $configData;

    /**
     * @param Config\Data $configData
     * @since 2.0.0
     */
    public function __construct(Config\Data $configData)
    {
        $this->configData = $configData;
    }

    /**
     * Get views list
     *
     * @return array[]
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getView($viewId)
    {
        return $this->configData->get($viewId);
    }
}
