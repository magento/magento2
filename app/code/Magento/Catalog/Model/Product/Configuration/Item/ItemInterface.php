<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Configuration\Item;

/**
 * Product configurational item interface
 */
interface ItemInterface
{
    /**
     * Retrieve associated product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct();

    /**
     * Get item option by code
     *
     * @param   string $code
     * @return  \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface
     */
    public function getOptionByCode($code);

    /**
     * Returns special download params (if needed) for custom option with type = 'file''
     * Return null, if not special params needed'
     * Or return \Magento\Framework\DataObject with any of the following indexes:
     *  - 'url' - url of controller to give the file
     *  - 'urlParams' - additional parameters for url (custom option id, or item id, for example)
     *
     * @return null|\Magento\Framework\DataObject
     */
    public function getFileDownloadParams();
}
