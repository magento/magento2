<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
