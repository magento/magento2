<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Wysiwyg;

/**
 * Interface ConfigInterface
 */
interface ConfigInterface
{
    /**
     * Return WYSIWYG configuration
     *
     * @return \Magento\Framework\DataObject
     */
    public function getConfig();
}
