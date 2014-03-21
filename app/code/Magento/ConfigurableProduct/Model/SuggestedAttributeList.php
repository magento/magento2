<?php
/**
 * List of suggested attributes
 *
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
namespace Magento\ConfigurableProduct\Model;

class SuggestedAttributeList
{
    /**
     * Attribute collection factory
     *
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory
     */
    protected $_attributeColFactory;

    /**
     * Catalog resource helper
     *
     * @var \Magento\Catalog\Model\Resource\Helper
     */
    protected $_resourceHelper;

    /**
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $attributeColFactory
     * @param \Magento\Catalog\Model\Resource\Helper $resourceHelper
     */
    public function __construct(
        \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $attributeColFactory,
        \Magento\Catalog\Model\Resource\Helper $resourceHelper
    ) {
        $this->_attributeColFactory = $attributeColFactory;
        $this->_resourceHelper = $resourceHelper;
    }

    /**
     * Retrieve list of attributes with admin store label containing $labelPart
     *
     * @param string $labelPart
     * @return \Magento\Catalog\Model\Resource\Product\Attribute\Collection
     */
    public function getSuggestedAttributes($labelPart)
    {
        $escapedLabelPart = $this->_resourceHelper->addLikeEscape($labelPart, array('position' => 'any'));
        /** @var $collection \Magento\Catalog\Model\Resource\Product\Attribute\Collection */
        $collection = $this->_attributeColFactory->create();
        $collection->addFieldToFilter(
            'frontend_input',
            'select'
        )->addFieldToFilter(
            'frontend_label',
            array('like' => $escapedLabelPart)
        )->addFieldToFilter(
            'is_configurable',
            array(array("eq" => 1), array('null' => true))
        )->addFieldToFilter(
            'is_user_defined',
            1
        )->addFieldToFilter(
            'is_global',
            \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_GLOBAL
        );

        $result = array();
        $types = array(
            \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
            \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
        );
        foreach ($collection->getItems() as $id => $attribute) {
            /** @var $attribute \Magento\Catalog\Model\Resource\Eav\Attribute */
            if (!$attribute->getApplyTo() || count(array_diff($types, $attribute->getApplyTo())) === 0) {
                $result[$id] = array(
                    'id' => $attribute->getId(),
                    'label' => $attribute->getFrontendLabel(),
                    'code' => $attribute->getAttributeCode(),
                    'options' => $attribute->getSource()->getAllOptions(false)
                );
            }
        }
        return $result;
    }
}
