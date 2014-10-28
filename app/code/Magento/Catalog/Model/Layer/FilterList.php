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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Model\Layer;

use Magento\Catalog\Model\Layer\Filter;

class FilterList
{
    const CATEGORY_FILTER   = 'category';
    const ATTRIBUTE_FILTER  = 'attribute';
    const PRICE_FILTER      = 'price';
    const DECIMAL_FILTER    = 'decimal';

    /**
     * Filter factory
     *
     * @var \Magento\Framework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var FilterableAttributeListInterface
     */
    protected $filterableAttributes;

    /**
     * @var string[]
     */
    protected $filterTypes = array(
        self::CATEGORY_FILTER  => 'Magento\Catalog\Model\Layer\Filter\Category',
        self::ATTRIBUTE_FILTER => 'Magento\Catalog\Model\Layer\Filter\Attribute',
        self::PRICE_FILTER     => 'Magento\Catalog\Model\Layer\Filter\Price',
        self::DECIMAL_FILTER   => 'Magento\Catalog\Model\Layer\Filter\Decimal',
    );

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\AbstractFilter[]
     */
    protected $filters = array();

    /**
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param FilterableAttributeListInterface $filterableAttributes
     * @param array $filters
     */
    public function __construct(
        \Magento\Framework\ObjectManager $objectManager,
        FilterableAttributeListInterface $filterableAttributes,
        array $filters = array()
    ) {
        $this->objectManager = $objectManager;
        $this->filterableAttributes = $filterableAttributes;

        /** Override default filter type models */
        $this->filterTypes = array_merge($this->filterTypes, $filters);
    }

    /**
     * Retrieve list of filters
     *
     * @param \Magento\Catalog\Model\Layer $layer
     * @return array|Filter\AbstractFilter[]
     */
    public function getFilters(\Magento\Catalog\Model\Layer $layer)
    {
        if (!count($this->filters)) {
            $this->filters = array(
                $this->objectManager->create($this->filterTypes[self::CATEGORY_FILTER], array('layer' => $layer)),
            );
            foreach ($this->filterableAttributes->getList() as $attibute) {
                $this->filters[] = $this->createAttributeFilter($attibute, $layer);
            }
        }
        return $this->filters;
    }

    /**
     * Create filter
     *
     * @param \Magento\Catalog\Model\Resource\Eav\Attribute $attribute
     * @param \Magento\Catalog\Model\Layer $layer
     * @return mixed
     */
    protected function createAttributeFilter(
        \Magento\Catalog\Model\Resource\Eav\Attribute $attribute,
        \Magento\Catalog\Model\Layer $layer
    ) {
        $filterClassName = $this->filterTypes[self::ATTRIBUTE_FILTER];

        if ($attribute->getAttributeCode() == 'price') {
            $filterClassName = $this->filterTypes[self::PRICE_FILTER];
        } elseif ($attribute->getBackendType() == 'decimal') {
            $filterClassName = $this->filterTypes[self::DECIMAL_FILTER];
        }

        $filter = $this->objectManager->create(
            $filterClassName,
            array('data' => array('attribute_model' => $attribute), 'layer' => $layer)
        );
        return $filter;
    }
}
