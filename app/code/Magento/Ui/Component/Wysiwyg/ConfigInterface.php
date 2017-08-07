<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Wysiwyg;

/**
 * Interface ConfigInterface
 * @since 2.1.0
 */
interface ConfigInterface
{
    /**
     * Return WYSIWYG configuration
     *
     * @return \Magento\Framework\DataObject
     * @since 2.1.0
     */
    public function getConfig();
}
