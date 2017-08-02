<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Weee\Block\Renderer\Weee;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Adminhtml weee tax item renderer
 * @since 2.0.0
 */
class Tax extends \Magento\Backend\Block\Widget implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * @var AbstractElement|null
     * @since 2.0.0
     */
    protected $_element = null;

    /**
     * @var array|null
     * @since 2.0.0
     */
    protected $_countries = null;

    /**
     * @var array|null
     * @since 2.0.0
     */
    protected $_websites = null;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'renderer/tax.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     * @since 2.0.0
     */
    protected $_sourceCountry;

    /**
     * @var \Magento\Directory\Helper\Data
     * @since 2.0.0
     */
    protected $_directoryHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Directory\Model\Config\Source\Country $sourceCountry
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Directory\Model\Config\Source\Country $sourceCountry,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_sourceCountry = $sourceCountry;
        $this->_directoryHelper = $directoryHelper;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Framework\DataObject
     * @since 2.0.0
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }

    /**
     * @param AbstractElement $element
     * @return string
     * @since 2.0.0
     */
    public function render(AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        $this->addChild(
            'add_button', \Magento\Backend\Block\Widget\Button::class,
            ['label' => __('Add Tax'), 'data_attribute' => ['action' => 'add-fpt-item'], 'class' => 'add']
        );
        $this->addChild(
            'delete_button', \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Delete Tax'),
                'data_attribute' => ['action' => 'delete-fpt-item'],
                'class' => 'delete'
            ]
        );
        return parent::_prepareLayout();
    }

    /**
     * @param AbstractElement $element
     * @return $this
     * @since 2.0.0
     */
    public function setElement(AbstractElement $element)
    {
        $this->_element = $element;
        return $this;
    }

    /**
     * @return AbstractElement|null
     * @since 2.0.0
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function getValues()
    {
        $values = [];
        $data = $this->getElement()->getEscapedValue();

        if (is_array($data) && count($data)) {
            usort($data, [$this, '_sortWeeeTaxes']);
            $values = $data;
        }
        return $values;
    }

    /**
     * @param array $firstItem
     * @param array $secondItem
     * @return int
     * @since 2.0.0
     */
    protected function _sortWeeeTaxes($firstItem, $secondItem)
    {
        if ($firstItem['website_id'] != $secondItem['website_id']) {
            return $firstItem['website_id'] < $secondItem['website_id'] ? -1 : 1;
        }
        if ($firstItem['country'] != $secondItem['country']) {
            return $firstItem['country'] < $secondItem['country'] ? -1 : 1;
        }
        return 0;
    }

    /**
     * @return int
     * @since 2.0.0
     */
    public function getWebsiteCount()
    {
        return count($this->getWebsites());
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    public function isMultiWebsites()
    {
        return !$this->_storeManager->hasSingleStore();
    }

    /**
     * @return array|null
     * @since 2.0.0
     */
    public function getCountries()
    {
        if (null === $this->_countries) {
            $this->_countries = $this->_sourceCountry->toOptionArray();
        }

        return $this->_countries;
    }

    /**
     * @return array|null
     * @since 2.0.0
     */
    public function getWebsites()
    {
        if (null !== $this->_websites) {
            return $this->_websites;
        }
        $websites = [];
        $websites[0] = [
            'name' => __('All Websites'),
            'currency' => $this->_directoryHelper->getBaseCurrencyCode(),
        ];

        if (!$this->_storeManager->hasSingleStore() && !$this->getElement()->getEntityAttribute()->isScopeGlobal()) {
            if ($storeId = $this->getProduct()->getStoreId()) {
                $website = $this->_storeManager->getStore($storeId)->getWebsite();
                $websites[$website->getId()] = [
                    'name' => $website->getName(),
                    'currency' => $website->getConfig(\Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE),
                ];
            } else {
                foreach ($this->_storeManager->getWebsites() as $website) {
                    if (!in_array($website->getId(), $this->getProduct()->getWebsiteIds())) {
                        continue;
                    }
                    $websites[$website->getId()] = [
                        'name' => $website->getName(),
                        'currency' => $website->getConfig(\Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE),
                    ];
                }
            }
        }
        $this->_websites = $websites;
        return $this->_websites;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }
}
