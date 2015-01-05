<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Eav\Api\Data;

interface AttributeFrontendLabelInterface
{
    /**
     * Return store id
     *
     * @return int|null
     */
    public function getStoreId();

    /**
     * Return label
     *
     * @return string|null
     */
    public function getLabel();
}
