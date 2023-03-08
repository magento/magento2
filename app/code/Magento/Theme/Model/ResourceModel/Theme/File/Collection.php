<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\ResourceModel\Theme\File;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\View\Design\Theme\File\CollectionInterface as ThemeFileCollectionInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Model\ResourceModel\Theme\File as ResourceThemeFile;
use Magento\Theme\Model\Theme\File as ModelThemeFile;

/**
 * Theme files collection
 */
class Collection extends AbstractCollection implements ThemeFileCollectionInterface
{
    /**
     * Collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ModelThemeFile::class, ResourceThemeFile::class);
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
     * @param ThemeInterface $theme
     * @return $this
     */
    public function addThemeFilter(ThemeInterface $theme)
    {
        $this->addFieldToFilter('theme_id', $theme->getId());
        return $this;
    }
}
