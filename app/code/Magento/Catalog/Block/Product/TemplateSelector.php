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
namespace Magento\Catalog\Block\Product;

/**
 * Product gallery
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class TemplateSelector extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Set collection factory
     *
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory
     */
    protected $_setColFactory;

    /**
     * Catalog resource helper
     *
     * @var \Magento\Catalog\Model\Resource\Helper
     */
    protected $_resourceHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $setColFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\Resource\Helper $resourceHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $setColFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Resource\Helper $resourceHelper,
        array $data = array()
    ) {
        $this->_setColFactory = $setColFactory;
        $this->_coreRegistry = $registry;
        $this->_resourceHelper = $resourceHelper;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve list of product templates with search part contained in label
     *
     * @param string $labelPart
     * @return array
     */
    public function getSuggestedTemplates($labelPart)
    {
        $product = $this->_coreRegistry->registry('product');
        $entityType = $product->getResource()->getEntityType();
        $labelPart = $this->_resourceHelper->addLikeEscape($labelPart, array('position' => 'any'));
        /** @var \Magento\Eav\Model\Resource\Entity\Attribute\Set\Collection $collection */
        $collection = $this->_setColFactory->create();
        $collection->setEntityTypeFilter(
            $entityType->getId()
        )->addFieldToFilter(
            'attribute_set_name',
            array('like' => $labelPart)
        )->addFieldToSelect(
            'attribute_set_id',
            'id'
        )->addFieldToSelect(
            'attribute_set_name',
            'label'
        )->setOrder(
            'attribute_set_name',
            \Magento\Eav\Model\Resource\Entity\Attribute\Set\Collection::SORT_ORDER_ASC
        );
        return $collection->getData();
    }
}
