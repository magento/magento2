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
namespace Magento\Weee\Block\Renderer\Weee;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Adminhtml weee tax item renderer
 */
class Tax extends \Magento\Backend\Block\Widget implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
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
    protected $_template = 'renderer/tax.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    protected $_sourceCountry;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Directory\Model\Config\Source\Country $sourceCountry
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Directory\Model\Config\Source\Country $sourceCountry,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Registry $registry,
        array $data = array()
    ) {
        $this->_sourceCountry = $sourceCountry;
        $this->_directoryHelper = $directoryHelper;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Framework\Object
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
            'Magento\Backend\Block\Widget\Button',
            array('label' => __('Add Tax'), 'data_attribute' => array('action' => 'add-fpt-item'), 'class' => 'add')
        );
        $this->addChild(
            'delete_button',
            'Magento\Backend\Block\Widget\Button',
            array(
                'label' => __('Delete Tax'),
                'data_attribute' => array('action' => 'delete-fpt-item'),
                'class' => 'delete'
            )
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
        $values = array();
        $data = $this->getElement()->getValue();

        if (is_array($data) && count($data)) {
            usort($data, array($this, '_sortWeeeTaxes'));
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
        $websites = array();
        $websites[0] = array(
            'name' => __('All Websites'),
            'currency' => $this->_directoryHelper->getBaseCurrencyCode()
        );

        if (!$this->_storeManager->hasSingleStore() && !$this->getElement()->getEntityAttribute()->isScopeGlobal()) {
            if ($storeId = $this->getProduct()->getStoreId()) {
                $website = $this->_storeManager->getStore($storeId)->getWebsite();
                $websites[$website->getId()] = array(
                    'name' => $website->getName(),
                    'currency' => $website->getConfig(\Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE)
                );
            } else {
                foreach ($this->_storeManager->getWebsites() as $website) {
                    if (!in_array($website->getId(), $this->getProduct()->getWebsiteIds())) {
                        continue;
                    }
                    $websites[$website->getId()] = array(
                        'name' => $website->getName(),
                        'currency' => $website->getConfig(\Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE)
                    );
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
