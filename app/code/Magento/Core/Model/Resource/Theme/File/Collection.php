<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Theme files collection
 */
namespace Magento\Core\Model\Resource\Theme\File;

class Collection extends \Magento\Core\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Collection initialization
     */
    protected function _construct()
    {
        $this->_init('Magento\Core\Model\Theme\File', 'Magento\Core\Model\Resource\Theme\File');
    }

    /**
     * Add select order
     *
     * $field is properly quoted, lately it was treated field "order" as special SQL word and was not working
     *
     * @param string $field
     * @param string $direction
     * @return \Magento\Core\Model\Resource\Theme\File\Collection|\Magento\Data\Collection|\Magento\Data\Collection\Db
     */
    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        return parent::setOrder($this->getConnection()->quoteIdentifier($field), $direction);
    }

    /**
     * Set default order
     *
     * @param string $direction
     * @return \Magento\Core\Model\Resource\Theme\File\Collection
     */
    public function setDefaultOrder($direction = self::SORT_ORDER_ASC)
    {
        return $this->setOrder('sort_order', $direction);
    }

    /**
     * Filter out files that do not belong to a theme
     *
     * @param \Magento\View\Design\ThemeInterface $theme
     * @return \Magento\Core\Model\Resource\Theme\File\Collection
     */
    public function addThemeFilter(\Magento\View\Design\ThemeInterface $theme)
    {
        $this->addFieldToFilter('theme_id', $theme->getId());
        return $this;
    }
}
