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

/**
 * New attribute panel on product edit page
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes;

class Search extends \Magento\Backend\Block\Widget
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Framework\DB\Helper
     */
    protected $_resourceHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\DB\Helper $resourceHelper,
        \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $registry,
        array $data = array()
    ) {
        $this->_resourceHelper = $resourceHelper;
        $this->_collectionFactory = $collectionFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Define block template
     *
     * @return void
     */
    protected function _construct()
    {
        $this->setTemplate('Magento_Catalog::product/edit/attribute/search.phtml');
        parent::_construct();
    }

    /**
     * @return array
     */
    public function getSelectorOptions()
    {
        $templateId = $this->_coreRegistry->registry('product')->getAttributeSetId();
        return array(
            'source' => $this->getUrl('catalog/product/suggestAttributes'),
            'minLength' => 0,
            'ajaxOptions' => array('data' => array('template_id' => $templateId)),
            'template' => '[data-template-for="product-attribute-search"]',
            'data' => $this->getSuggestedAttributes('', $templateId)
        );
    }

    /**
     * Retrieve list of attributes with admin store label containing $labelPart
     *
     * @param string $labelPart
     * @param int $templateId
     * @return \Magento\Catalog\Model\Resource\Product\Attribute\Collection
     */
    public function getSuggestedAttributes($labelPart, $templateId = null)
    {
        $escapedLabelPart = $this->_resourceHelper->addLikeEscape(
            $labelPart,
            array('position' => 'any')
        );
        /** @var $collection \Magento\Catalog\Model\Resource\Product\Attribute\Collection */
        $collection = $this->_collectionFactory->create()->addFieldToFilter(
            'frontend_label',
            array('like' => $escapedLabelPart)
        );

        $collection->setExcludeSetFilter($templateId ?: $this->getRequest()->getParam('template_id'))->setPageSize(20);

        $result = array();
        foreach ($collection->getItems() as $attribute) {
            /** @var $attribute \Magento\Catalog\Model\Resource\Eav\Attribute */
            $result[] = array(
                'id' => $attribute->getId(),
                'label' => $attribute->getFrontendLabel(),
                'code' => $attribute->getAttributeCode()
            );
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getAddAttributeUrl()
    {
        return $this->getUrl('catalog/product/addAttributeToTemplate');
    }
}
