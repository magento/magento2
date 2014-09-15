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
namespace Magento\Catalog\Model\Layer\Filter;

/**
 * Layer attribute filter
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Attribute extends \Magento\Catalog\Model\Layer\Filter\AbstractFilter
{
    const OPTIONS_ONLY_WITH_RESULTS = 1;

    /**
     * Resource instance
     *
     * @var \Magento\Catalog\Model\Resource\Layer\Filter\Attribute
     */
    protected $_resource;

    /**
     * Magento string lib
     *
     * @var \Magento\Framework\Stdlib\String
     */
    protected $string;

    /**
     * @var \Magento\Framework\Filter\StripTags
     */
    protected $tagFilter;

    /**
     * @param ItemFactory $filterItemFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $layer
     * @param \Magento\Catalog\Model\Resource\Layer\Filter\AttributeFactory $filterAttributeFactory
     * @param \Magento\Framework\Stdlib\String $string
     * @param \Magento\Framework\Filter\StripTags $tagFilter
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Resource\Layer\Filter\AttributeFactory $filterAttributeFactory,
        \Magento\Framework\Stdlib\String $string,
        \Magento\Framework\Filter\StripTags $tagFilter,
        array $data = array()
    ) {
        $this->_resource = $filterAttributeFactory->create();
        $this->string = $string;
        $this->_requestVar = 'attribute';
        $this->tagFilter = $tagFilter;
        parent::__construct($filterItemFactory, $storeManager, $layer, $data);
    }

    /**
     * Retrieve resource instance
     *
     * @return \Magento\Catalog\Model\Resource\Layer\Filter\Attribute
     */
    protected function _getResource()
    {
        return $this->_resource;
    }

    /**
     * Get option text from frontend model by option id
     *
     * @param   int $optionId
     * @return  string|bool
     */
    protected function _getOptionText($optionId)
    {
        return $this->getAttributeModel()->getFrontend()->getOption($optionId);
    }

    /**
     * Apply attribute option filter to product collection
     *
     * @param   \Zend_Controller_Request_Abstract $request
     * @return  $this
     */
    public function apply(\Zend_Controller_Request_Abstract $request)
    {
        $filter = $request->getParam($this->_requestVar);
        if (is_array($filter)) {
            return $this;
        }
        $text = $this->_getOptionText($filter);
        if ($filter && strlen($text)) {
            $this->_getResource()->applyFilterToCollection($this, $filter);
            $this->getLayer()->getState()->addFilter($this->_createItem($text, $filter));
            $this->_items = array();
        }
        return $this;
    }

    /**
     * Check whether specified attribute can be used in LN
     *
     * @param \Magento\Catalog\Model\Resource\Eav\Attribute $attribute
     * @return bool
     */
    protected function _getIsFilterableAttribute($attribute)
    {
        return $attribute->getIsFilterable();
    }

    /**
     * Get data array for building attribute filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        $attribute = $this->getAttributeModel();
        $this->_requestVar = $attribute->getAttributeCode();

        $options = $attribute->getFrontend()->getSelectOptions();
        $optionsCount = $this->_getResource()->getCount($this);
        $data = array();
        foreach ($options as $option) {
            if (is_array($option['value'])) {
                continue;
            }
            if ($this->string->strlen($option['value'])) {
                // Check filter type
                if ($this->_getIsFilterableAttribute($attribute) == self::OPTIONS_ONLY_WITH_RESULTS) {
                    if (!empty($optionsCount[$option['value']])) {
                        $data[] = array(
                            'label' => $this->tagFilter->filter($option['label']),
                            'value' => $option['value'],
                            'count' => $optionsCount[$option['value']]
                        );
                    }
                } else {
                    $data[] = array(
                        'label' => $this->tagFilter->filter($option['label']),
                        'value' => $option['value'],
                        'count' => isset($optionsCount[$option['value']]) ? $optionsCount[$option['value']] : 0
                    );
                }
            }
        }

        return $data;
    }
}
