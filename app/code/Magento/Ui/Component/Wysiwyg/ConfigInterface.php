<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
