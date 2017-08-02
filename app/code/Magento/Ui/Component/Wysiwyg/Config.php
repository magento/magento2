<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Wysiwyg;

/**
 * Class Config
 * @since 2.1.0
 */
class Config implements ConfigInterface
{
    /**
     * Return WYSIWYG configuration
     *
     * @return \Magento\Framework\DataObject
     * @since 2.1.0
     */
    public function getConfig()
    {
        return [];
    }
}
