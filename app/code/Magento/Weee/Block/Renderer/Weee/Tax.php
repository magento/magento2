<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Block\Renderer\Weee;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;
use Magento\Backend\Block\Widget\Button;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Directory\Model\Config\Source\Country;
use Magento\Directory\Model\Currency;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;

/**
 * Adminhtml weee tax item renderer
 */
class Tax extends Widget implements RendererInterface
{
    /**
     * @var AbstractElement|null
     */
    protected $_element = null;

    /**
     * @var array|null
     */
    protected $_countries = null;

    /**
     * @var array|null
     */
    protected $_websites = null;

    /**
     * @var string
     */
    protected $_template = 'Magento_Weee::renderer/tax.phtml';

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var Country
     */
    protected $_sourceCountry;

    /**
     * @var DirectoryHelper
     */
    protected $_directoryHelper;

    /**
     * @param Context $context
     * @param Country $sourceCountry
     * @param DirectoryHelper $directoryHelper
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context        $context,
        Country $sourceCountry,
        DirectoryHelper $directoryHelper,
        Registry $registry,
        array $data = []
    ) {
        $this->_sourceCountry = $sourceCountry;
        $this->_directoryHelper = $directoryHelper;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return DataObject
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        $this->addChild(
            'add_button',
            Button::class,
            ['label' => __('Add Tax'), 'data_attribute' => ['action' => 'add-fpt-item'], 'class' => 'add']
        );
        $this->addChild(
            'delete_button',
            Button::class,
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
     */
    public function setElement(AbstractElement $element)
    {
        $this->_element = $element;
        return $this;
    }

    /**
     * @return AbstractElement|null
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * @return array
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
     */
    public function getWebsiteCount()
    {
        return count($this->getWebsites());
    }

    /**
     * @return bool
     */
    public function isMultiWebsites()
    {
        return !$this->_storeManager->hasSingleStore();
    }

    /**
     * @return array|null
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
                    'currency' => $website->getConfig(Currency::XML_PATH_CURRENCY_BASE),
                ];
            } else {
                foreach ($this->_storeManager->getWebsites() as $website) {
                    if (!in_array($website->getId(), $this->getProduct()->getWebsiteIds())) {
                        continue;
                    }
                    $websites[$website->getId()] = [
                        'name' => $website->getName(),
                        'currency' => $website->getConfig(Currency::XML_PATH_CURRENCY_BASE),
                    ];
                }
            }
        }
        $this->_websites = $websites;
        return $this->_websites;
    }

    /**
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }
}
