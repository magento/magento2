<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Ui\Component\Wysiwyg;

/**
 * Interface ConfigInterface
 *
 * @api
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
