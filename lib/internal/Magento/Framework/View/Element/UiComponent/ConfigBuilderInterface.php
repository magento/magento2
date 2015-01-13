<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent;

/**
 * Interface ConfigBuilderInterface
 */
interface ConfigBuilderInterface
{
    /**
     * Config data to JSON by output
     *
     * @param ConfigInterface $configuration
     * @return string
     */
    public function toJson(ConfigInterface $configuration);
}
