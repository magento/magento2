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
 * @package     Magento_Adminhtml
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Block\Adminhtml\Product\Helper\Form;
use Magento\Catalog\Model\Resource\Category\Collection;

/**
 * Product form category field helper
 */
class Category extends \Magento\Data\Form\Element\Multiselect
{
    /**
     * @var \Magento\View\LayoutInterface
     */
    protected $_layout;

    /**
     * Backend data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backendData;

    /**
     * @var \Magento\Catalog\Model\Resource\Category\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param \Magento\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Escaper $escaper
     * @param \Magento\Catalog\Model\Resource\Category\CollectionFactory $collectionFactory
     * @param \Magento\Backend\Helper\Data $backendData
     * @param \Magento\View\LayoutInterface $layout
     * @param \Magento\Json\EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        \Magento\Data\Form\Element\Factory $factoryElement,
        \Magento\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Escaper $escaper,
        \Magento\Catalog\Model\Resource\Category\CollectionFactory $collectionFactory,
        \Magento\Backend\Helper\Data $backendData,
        \Magento\View\LayoutInterface $layout,
        \Magento\Json\EncoderInterface $jsonEncoder,
        array $data = array()
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_collectionFactory = $collectionFactory;
        $this->_backendData = $backendData;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->_layout = $layout;
    }

    /**
     * Get values for select
     *
     * @return array
     */
    public function getValues()
    {
        $collection = $this->_getCategoriesCollection();
        $values = $this->getValue();
        if (!is_array($values)) {
            $values = explode(',', $values);
        }
        $collection->addAttributeToSelect('name');
        $collection->addIdFilter($values);

        $options = array();

        foreach ($collection as $category) {
            $options[] = array(
                'label' => $category->getName(),
                'value' => $category->getId()
            );
        }
        return $options;
    }

    /**
     * Get categories collection
     *
     * @return Collection
     */
    protected function _getCategoriesCollection()
    {
        return $this->_collectionFactory->create();
    }

    /**
     * Attach category suggest widget initialization
     *
     * @return string
     */
    public function getAfterElementHtml()
    {
        $htmlId = $this->getHtmlId();
        $suggestPlaceholder = __('start typing to search category');
        $selectorOptions = $this->_jsonEncoder->encode($this->_getSelectorOptions());
        $newCategoryCaption = __('New Category');

        $button = $this->_layout
            ->createBlock('Magento\Backend\Block\Widget\Button')
            ->setData(array(
                'id' => 'add_category_button',
                'label' => $newCategoryCaption,
                'title' => $newCategoryCaption,
                'onclick' => 'jQuery("#new-category").dialog("open")',
                'disabled' => $this->getDisabled(),
            ));
        $return = <<<HTML
    <input id="{$htmlId}-suggest" placeholder="$suggestPlaceholder" />
    <script>
        jQuery('#{$htmlId}-suggest').mage('treeSuggest', {$selectorOptions});
    </script>
HTML;
        return $return . $button->toHtml();
    }

    /**
     * Get selector options
     *
     * @return array
     */
    protected function _getSelectorOptions()
    {
        return array(
            'source' => $this->_backendData
                ->getUrl('catalog/category/suggestCategories'),
            'valueField' => '#' . $this->getHtmlId(),
            'className' => 'category-select',
            'multiselect' => true,
            'showAll' => true
        );
    }
}
