<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Block\Adminhtml\Types\Edit;

/**
 * Attributes box for Google Content attributes mapping
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Attributes extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element
{
    /**
     * @var string
     */
    protected $_template = 'types/edit/attributes.phtml';

    /**
     * Config
     *
     * @var \Magento\GoogleShopping\Model\Config
     */
    protected $_config;

    /**
     * Attribute factory
     *
     * @var \Magento\GoogleShopping\Model\AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\GoogleShopping\Model\Config $config
     * @param \Magento\GoogleShopping\Model\AttributeFactory $attributeFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\GoogleShopping\Model\Config $config,
        \Magento\GoogleShopping\Model\AttributeFactory $attributeFactory,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_config = $config;
        $this->_attributeFactory = $attributeFactory;
        parent::__construct($context, $data);
    }

    /**
     * Preparing global layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->addChild(
            'add_button',
            'Magento\Backend\Block\Widget\Button',
            [
                'label' => __('Add New Attribute'),
                'class' => 'add',
                'id' => 'add_new_attribute',
                'on_click' => 'gContentAttribute.add()'
            ]
        );
        $this->addChild(
            'delete_button',
            'Magento\Backend\Block\Widget\Button',
            [
                'label' => __('Remove'),
                'class' => 'delete delete-product-option',
                'on_click' => 'gContentAttribute.remove(event)'
            ]
        );

        return parent::_prepareLayout();
    }

    /**
     * Get attributes select field id
     *
     * @return string
     */
    public function getFieldId()
    {
        return 'gcontent_attribute';
    }

    /**
     * Get attributes select field name
     *
     * @return string
     */
    public function getFieldName()
    {
        return 'attributes';
    }

    /**
     * Build HTML code for select element which contains all available Google's attributes
     *
     * @return string
     */
    public function getGcontentAttributesSelectHtml()
    {
        $options[] = ['label' => __('Custom attribute, no mapping')];

        $attributesTree = $this->_config->getAttributesByCountry($this->getTargetCountry());

        foreach ($attributesTree as $destination => $attributes) {
            $options[] = ['label' => $destination, 'is_group' => true];
            foreach ($attributes as $attribute => $params) {
                $options[$attribute] = ['label' => $params['name']];
                if ((int)$params['required'] == 1) {
                    $options[$attribute]['style'] = 'color: #940000;';
                }
            }
            $options[] = ['is_group' => true, 'is_close' => true];
        }

        $select = $this->getLayout()->createBlock(
            'Magento\GoogleShopping\Block\Adminhtml\Types\Edit\Select'
        )->setId(
            $this->getFieldId() . '_{{index}}_gattribute'
        )->setName(
            $this->getFieldName() . '[{{index}}][gcontent_attribute]'
        )->setOptions(
            $options
        );

        return $this->_toOneLineString($select->toHtml());
    }

    /**
     * Build HTML select element of attribute set attributes
     *
     * @param bool $escapeJsQuotes
     * @return string
     */
    public function getAttributesSelectHtml($escapeJsQuotes = false)
    {
        $select = $this->getLayout()->createBlock(
            'Magento\Framework\View\Element\Html\Select'
        )->setId(
            $this->getFieldId() . '_{{index}}_attribute'
        )->setName(
            $this->getFieldName() . '[{{index}}][attribute_id]'
        )->setOptions(
            $this->_getAttributes($this->getAttributeSetId(), $escapeJsQuotes)
        );
        return $select->getHtml();
    }

    /**
     * Get HTML code for button "Add New Attribute"
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    /**
     * Get HTML code for button "Remove"
     *
     * @return string
     */
    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    /**
     * Get attributes of an attribute set
     * Skip attributes not needed for Google Content
     *
     * @param int $setId
     * @param bool $escapeJsQuotes
     * @return array
     */
    public function _getAttributes($setId, $escapeJsQuotes = false)
    {
        $attributes = $this->_attributeFactory->create()->getAllowedAttributes($setId);
        $result = [];

        foreach ($attributes as $attribute) {
            /* @var $attribute \Magento\Catalog\Model\Resource\Eav\Attribute */
            $result[$attribute->getAttributeId()] = $escapeJsQuotes ? $this->escapeJsQuote(
                $attribute->getFrontendLabel()
            ) : $attribute->getFrontendLabel();
        }
        return $result;
    }

    /**
     * Encode the mixed $data into the JSON format
     *
     * @param mixed $data
     * @return string
     */
    public function jsonFormat($data)
    {
        return $this->_jsonEncoder->encode($data);
    }

    /**
     * Format string to one line, cut symbols \n and \r
     *
     * @param string $string
     * @return string
     */
    protected function _toOneLineString($string)
    {
        return str_replace(["\r\n", "\n", "\r"], "", $string);
    }
}
