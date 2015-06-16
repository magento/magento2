<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\DataProvider;

abstract class AbstractEavDataProvider extends AbstractDataProvider
{
    /**
     * Add field to select
     *
     * @param string|array $field
     * @param string|null $alias
     * @return void
     */
    public function addField($field, $alias = null)
    {
        $this->getCollection()->addAttributeToSelect($field);
    }

    /**
     * Removes field from select
     *
     * @param string|null $field
     * @param bool $isAlias Alias identifier
     * @return void
     */
    public function removeField($field, $isAlias = false)
    {
        $this->getCollection()->removeAttributeToSelect($field);
    }

    /**
     * Removes all fields from select
     *
     * @return void
     */
    public function removeAllFields()
    {
        $this->getCollection()->removeAttributeToSelect();
    }
}
