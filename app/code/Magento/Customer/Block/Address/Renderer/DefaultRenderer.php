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
 * @package     Magento_Customer
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Address format renderer default
 *
 * @category   Magento
 * @package    Magento_Customer
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Customer\Block\Address\Renderer;

class DefaultRenderer
    extends \Magento\Core\Block\AbstractBlock
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
     * @var \Magento\Eav\Model\AttributeDataFactory
     */
    protected $_attrDataFactory;

    /**
     * @param \Magento\Customer\Helper\Address $customerAddress
     * @param \Magento\Core\Block\Context $context
     * @param \Magento\Eav\Model\AttributeDataFactory $attrDataFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Helper\Address $customerAddress,
        \Magento\Core\Block\Context $context,
        \Magento\Eav\Model\AttributeDataFactory $attrDataFactory,
        array $data = array()
    ) {
        $this->_customerAddress = $customerAddress;
        $this->_attrDataFactory = $attrDataFactory;
        parent::__construct($context, $data);
    }

    /**
     * Retrive format type object
     *
     * @return \Magento\Object
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Retrive format type object
     *
     * @param  \Magento\Object $type
     * @return \Magento\Customer\Block\Address\Renderer\DefaultRenderer
     */
    public function setType(\Magento\Object $type)
    {
        $this->_type = $type;
        return $this;
    }

    public function getFormat(\Magento\Customer\Model\Address\AbstractAddress $address=null)
    {
        $countryFormat = is_null($address)
            ? false
            : $address->getCountryModel()->getFormat($this->getType()->getCode());
        $format = $countryFormat ? $countryFormat->getFormat() : $this->getType()->getDefaultFormat();
        return $format;
    }

    /**
     * Render address
     *
     * @param \Magento\Customer\Model\Address\AbstractAddress $address
     * @return string
     */
    public function render(\Magento\Customer\Model\Address\AbstractAddress $address, $format=null)
    {
        switch ($this->getType()->getCode()) {
            case 'html':
                $dataFormat = \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_HTML;
                break;
            case 'pdf':
                $dataFormat = \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_PDF;
                break;
            case 'oneline':
                $dataFormat = \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_ONELINE;
                break;
            default:
                $dataFormat = \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_TEXT;
                break;
        }

        $formater   = new \Magento\Filter\Template();
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
                    $values    = $dataModel->outputValue(\Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_ARRAY);
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

        $formater->setVariables($data);

        $format = !is_null($format) ? $format : $this->getFormat($address);

        return $formater->filter($format);
    }
}
