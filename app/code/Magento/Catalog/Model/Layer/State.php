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
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Layer;

/**
 * Layered navigation state model
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class State extends \Magento\Object
{
    /**
     * Add filter item to layer state
     *
     * @param   \Magento\Catalog\Model\Layer\Filter\Item $filter
     * @return  $this
     */
    public function addFilter($filter)
    {
        $filters = $this->getFilters();
        $filters[] = $filter;
        $this->setFilters($filters);
        return $this;
    }

    /**
     * Set layer state filter items
     *
     * @param  array $filters
     * @return $this
     * @throws \Magento\Core\Exception
     */
    public function setFilters($filters)
    {
        if (!is_array($filters)) {
            throw new \Magento\Core\Exception(__('The filters must be an array.'));
        }
        $this->setData('filters', $filters);
        return $this;
    }

    /**
     * Get applied to layer filter items
     *
     * @return array
     */
    public function getFilters()
    {
        $filters = $this->getData('filters');
        if (is_null($filters)) {
            $filters = array();
            $this->setData('filters', $filters);
        }
        return $filters;
    }
}
