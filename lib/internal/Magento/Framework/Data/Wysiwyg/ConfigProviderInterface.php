<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Wysiwyg;

/**
 * Interface ConfigProviderInterface
 * @api
 * @since 102.0.0
 */
interface ConfigProviderInterface
{
    /**
     * @param \Magento\Framework\DataObject $config
     * @return \Magento\Framework\DataObject
     * @since 102.0.0
     */
    public function getConfig(\Magento\Framework\DataObject $config) : \Magento\Framework\DataObject;
}
