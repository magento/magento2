<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Wysiwyg;

use Magento\Framework\DataObject;

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
     * @return DataObject
     */
    public function getConfig();
}
