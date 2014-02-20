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
namespace Magento\Customer\Block\Address\Renderer;

use Magento\Eav\Model\AttributeDataFactory;
use Magento\Customer\Model\Metadata\ElementFactory;

/**
 * Address format renderer default
 */
class DefaultRenderer
    extends \Magento\View\Element\AbstractBlock
    implements \Magento\Customer\Block\Address\Renderer\RendererInterface
{
    /**
     * Format type object
     *
     * @var \Magento\Object
     */
    protected $_type;

    /**
     * Customer address
     *
     * @var \Magento\Customer\Helper\Address
     */
    protected $_customerAddress = null;

    /**
     * @var ElementFactory
     */
    protected $_elementFactory;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $_countryFactory;

    /**
     * @var \Magento\Customer\Service\V1\CustomerMetadataServiceInterface
     */
    protected $_metadataService;

    /**
     * @var AttributeDataFactory
     */
    protected $_attrDataFactory;

    /**
     * @param \Magento\View\Element\Context $context
     * @param \Magento\Customer\Model\Metadata\ElementFactory $elementFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory ,
     * @param \Magento\Customer\Helper\Address $customerAddress
     * @param \Magento\Customer\Service\V1\CustomerMetadataServiceInterface $metadataService
     * @param AttributeDataFactory $attrDataFactory
     * @param array $data
     */
    public function __construct(
        \Magento\View\Element\Context $context,
        \Magento\Customer\Model\Metadata\ElementFactory $elementFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Customer\Helper\Address $customerAddress,
        \Magento\Customer\Service\V1\CustomerMetadataServiceInterface $metadataService,
        AttributeDataFactory $attrDataFactory,
        array $data = array()
    ) {
        $this->_elementFactory = $elementFactory;
        $this->_customerAddress = $customerAddress;
        $this->_countryFactory = $countryFactory;
        $this->_metadataService = $metadataService;
        $this->_attrDataFactory = $attrDataFactory;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Retrieve format type object
     *
     * @return \Magento\Object
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Retrieve format type object
     *
     * @param  \Magento\Object $type
     * @return \Magento\Customer\Block\Address\Renderer\DefaultRenderer
     */
    public function setType(\Magento\Object $type)
    {
        $this->_type = $type;
        return $this;
    }

    /**
     * @deprecated All new code should use renderArray based on Metadata service
     * @param \Magento\Customer\Model\Address\AbstractAddress $address
     * @return string
     */
    public function getFormat(\Magento\Customer\Model\Address\AbstractAddress $address = null)
    {
        $countryFormat = is_null($address)
            ? false
            : $address->getCountryModel()->getFormat($this->getType()->getCode());
        $format = $countryFormat ? $countryFormat->getFormat() : $this->getType()->getDefaultFormat();
        return $format;
    }

    /**
     * {@inheritdoc}
     */
    public function render(\Magento\Customer\Model\Address\AbstractAddress $address, $format = null)
    {
        switch ($this->getType()->getCode()) {
            case 'html':
                $dataFormat = AttributeDataFactory::OUTPUT_FORMAT_HTML;
                break;
            case 'pdf':
                $dataFormat = AttributeDataFactory::OUTPUT_FORMAT_PDF;
                break;
            case 'oneline':
                $dataFormat = AttributeDataFactory::OUTPUT_FORMAT_ONELINE;
                break;
            default:
                $dataFormat = AttributeDataFactory::OUTPUT_FORMAT_TEXT;
                break;
        }

        $attributes = $this->_customerAddress->getAttributes();

        $data = array();
        foreach ($attributes as $attribute) {
            /* @var $attribute \Magento\Customer\Model\Attribute */
            if (!$attribute->getIsVisible()) {
                continue;
            }
            if ($attribute->getAttributeCode() == 'country_id') {
                $data['country'] = $address->getCountryModel()->getName();
            } else if ($attribute->getAttributeCode() == 'region') {
                $data['region'] = __($address->getRegion());
            } else {
                $dataModel = $this->_attrDataFactory->create($attribute, $address);
                $value     = $dataModel->outputValue($dataFormat);
                if ($attribute->getFrontendInput() == 'multiline') {
                    $values    = $dataModel->outputValue(AttributeDataFactory::OUTPUT_FORMAT_ARRAY);
                    // explode lines
                    foreach ($values as $k => $v) {
                        $key = sprintf('%s%d', $attribute->getAttributeCode(), $k + 1);
                        $data[$key] = $v;
                    }
                }
                $data[$attribute->getAttributeCode()] = $value;
            }
        }

        if ($this->getType()->getEscapeHtml()) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->escapeHtml($value);
            }
        }
        $format = !is_null($format) ? $format : $this->getFormat($address);

        return $this->filterManager->template($format, array('variables' => $data));
    }

    /**
     * {@inheritdoc}
     */
    public function getFormatArray($addressAttributes = null)
    {
        $countryFormat = false;
        if ($addressAttributes && isset($addressAttributes['country_id'])) {
            /** @var \Magento\Directory\Model\Country $country */
            $country = $this->_countryFactory->create()->load($addressAttributes['country_id']);
            $countryFormat = $country->getFormat($this->getType()->getCode());
        }
        $format = $countryFormat ? $countryFormat->getFormat() : $this->getType()->getDefaultFormat();
        return $format;
    }

    /**
     * {@inheritdoc}
     */
    public function renderArray($addressAttributes, $format = null)
    {
        switch ($this->getType()->getCode()) {
            case 'html':
                $dataFormat = AttributeDataFactory::OUTPUT_FORMAT_HTML;
                break;
            case 'pdf':
                $dataFormat = AttributeDataFactory::OUTPUT_FORMAT_PDF;
                break;
            case 'oneline':
                $dataFormat = AttributeDataFactory::OUTPUT_FORMAT_ONELINE;
                break;
            default:
                $dataFormat = AttributeDataFactory::OUTPUT_FORMAT_TEXT;
                break;
        }

        $attributesMetadata = $this->_metadataService->getAllAddressAttributeMetadata();
        $data = array();
        foreach ($attributesMetadata as $attributeMetadata) {
            if (!$attributeMetadata->isVisible()) {
                continue;
            }
            $attributeCode = $attributeMetadata->getAttributeCode();
            if ($attributeCode == 'country_id' && isset($addressAttributes['country_id'])) {
                $data['country'] =
                    $this->_countryFactory->create(['id' => $addressAttributes['country_id']])->getName();
            } elseif ($attributeCode == 'region' && isset($addressAttributes['region'])) {
                $data['region'] = __($addressAttributes['region']['region']);
            } elseif (isset($addressAttributes[$attributeCode])) {
                $value = $addressAttributes[$attributeCode];
                $dataModel = $this->_elementFactory->create($attributeMetadata, $value, 'customer_address');
                $value = $dataModel->outputValue($dataFormat);
                if ($attributeMetadata->getFrontendInput() == 'multiline') {
                    $values = $dataModel->outputValue(AttributeDataFactory::OUTPUT_FORMAT_ARRAY);
                    // explode lines
                    foreach ($values as $k => $v) {
                        $key = sprintf('%s%d', $attributeCode, $k + 1);
                        $data[$key] = $v;
                    }
                }
                $data[$attributeCode] = $value;
            }
        }
        if ($this->getType()->getEscapeHtml()) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->escapeHtml($value);
            }
        }
        $format = !is_null($format) ? $format : $this->getFormatArray($addressAttributes);
        return $this->filterManager->template($format, array('variables' => $data));
    }
}
