<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent;

/**
 * Interface ConfigStorageBuilderInterface
 */
interface ConfigStorageBuilderInterface
{
    /**
     * Config storage data to JSON by output
     *
     * @param ConfigStorageInterface $storage
     * @param string $parentName
     * @return string
     */
    public function toJson(ConfigStorageInterface $storage, $parentName = null);
}
