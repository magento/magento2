<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Model\Wysiwyg;

/**
 * Interface ConfigInterface
 * @package Magento\Config\Model\Wysiwyg
 */
interface ConfigInterface
{
    /**
     * @param \Magento\Framework\DataObject $config
     * @return \Magento\Framework\DataObject|array
     */
    public function getConfig($config);
}
