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

use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Customer\Model\Metadata\ElementFactory;
use Magento\Framework\View\Element\AbstractBlock;

/**
 * Address format renderer default
 */
class DefaultRenderer extends AbstractBlock implements RendererInterface
{
    /**
     * Format type object
     *
     * @var \Magento\Framework\Object
     */
    protected $_type;

    /**
     * @var ElementFactory
     */
    protected $_elementFactory;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $_countryFactory;

    /**
     * @var \Magento\Customer\Service\V1\AddressMetadataServiceInterface
     */
    protected $_addressMetadataService;

    /**
     * Address converter
     *
     * @var \Magento\Customer\Model\Address\Converter
     */
    protected $_addressConverter;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param ElementFactory $elementFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory ,
     * @param \Magento\Customer\Model\Address\Converter $addressConverter
     * @param \Magento\Customer\Service\V1\AddressMetadataServiceInterface $metadataService
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        ElementFactory $elementFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Customer\Model\Address\Converter $addressConverter,
        \Magento\Customer\Service\V1\AddressMetadataServiceInterface $metadataService,
        array $data = array()
    ) {
        $this->_elementFactory = $elementFactory;
        $this->_addressConverter = $addressConverter;
        $this->_countryFactory = $countryFactory;
        $this->_addressMetadataService = $metadataService;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Retrieve format type object
     *
     * @return \Magento\Framework\Object
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Retrieve format type object
     *
     * @param  \Magento\Framework\Object $type
     * @return $this
     */
    public function setType(\Magento\Framework\Object $type)
    {
        $this->_type = $type;
        return $this;
    }

    /**
     * @param AbstractAddress|null $address
     * @return string
     * @deprecated All new code should use renderArray based on Metadata service
     */
    public function getFormat(AbstractAddress $address = null)
    {
        $countryFormat = is_null(
            $address
        ) ? false : $address->getCountryModel()->getFormat(
            $this->getType()->getCode()
        );
        $format = $countryFormat ? $countryFormat->getFormat() : $this->getType()->getDefaultFormat();
        return $format;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function render(AbstractAddress $address, $format = null)
    {
        $address = $this->_addressConverter->createAddressFromModel($address, 0, 0);
        return $this->renderArray(\Magento\Customer\Service\V1\Data\AddressConverter::toFlatArray($address), $format);
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
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function renderArray($addressAttributes, $format = null)
    {
        switch ($this->getType()->getCode()) {
            case 'html':
                $dataFormat = ElementFactory::OUTPUT_FORMAT_HTML;
                break;
            case 'pdf':
                $dataFormat = ElementFactory::OUTPUT_FORMAT_PDF;
                break;
            case 'oneline':
                $dataFormat = ElementFactory::OUTPUT_FORMAT_ONELINE;
                break;
            default:
                $dataFormat = ElementFactory::OUTPUT_FORMAT_TEXT;
                break;
        }

        $attributesMetadata = $this->_addressMetadataService->getAllAttributesMetadata();
        $data = array();
        foreach ($attributesMetadata as $attributeMetadata) {
            if (!$attributeMetadata->isVisible()) {
                continue;
            }
            $attributeCode = $attributeMetadata->getAttributeCode();
            if ($attributeCode == 'country_id' && isset($addressAttributes['country_id'])) {
                $data['country'] = $this->_countryFactory->create()->loadByCode(
                    $addressAttributes['country_id']
                )->getName();
            } elseif ($attributeCode == 'region' && isset($addressAttributes['region'])) {
                $data['region'] = __($addressAttributes['region']);
            } elseif (isset($addressAttributes[$attributeCode])) {
                $value = $addressAttributes[$attributeCode];
                $dataModel = $this->_elementFactory->create($attributeMetadata, $value, 'customer_address');
                $value = $dataModel->outputValue($dataFormat);
                if ($attributeMetadata->getFrontendInput() == 'multiline') {
                    $values = $dataModel->outputValue(ElementFactory::OUTPUT_FORMAT_ARRAY);
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
