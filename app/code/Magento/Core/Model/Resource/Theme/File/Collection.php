<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Model\Resource\Theme\File;

/**
 * Theme files collection
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection implements
    \Magento\Framework\View\Design\Theme\File\CollectionInterface
{
    /**
     * Collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Core\Model\Theme\File', 'Magento\Core\Model\Resource\Theme\File');
    }

    /**
     * Add select order
     *
     * The $field parameter is properly quoted, lately it was treated field "order" as special SQL
     * word and was not working
     *
     * @param string $field
     * @param string $direction
     * @return $this
     */
    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        return parent::setOrder($this->getConnection()->quoteIdentifier($field), $direction);
    }

    /**
     * Set default order
     *
     * @param string $direction
     * @return $this
     */
    public function setDefaultOrder($direction = self::SORT_ORDER_ASC)
    {
        return $this->setOrder('sort_order', $direction);
    }

    /**
     * Filter out files that do not belong to a theme
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @return $this
     */
    public function addThemeFilter(\Magento\Framework\View\Design\ThemeInterface $theme)
    {
        $this->addFieldToFilter('theme_id', $theme->getId());
        return $this;
    }
}
