<?php
/**
 * Adminhtml catalog super product link grid checkbox renderer
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Tab\Super\Config\Grid\Renderer;

class Checkbox extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Checkbox
{
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Backend\Block\Widget\Grid\Column\Renderer\Options\Converter $converter
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Block\Widget\Grid\Column\Renderer\Options\Converter $converter,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        parent::__construct($context, $converter, $data);
    }

    /**
     * Renders grid column
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function render(\Magento\Framework\Object $row)
    {
        $result = parent::render($row);
        return $result . '<input type="hidden" class="value-json" value="' . htmlspecialchars(
            $this->getAttributesJson($row)
        ) . '" />';
    }

    /**
     * Get attributes json
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function getAttributesJson(\Magento\Framework\Object $row)
    {
        if (!$this->getColumn()->getAttributes()) {
            return '[]';
        }

        $result = [];
        foreach ($this->getColumn()->getAttributes() as $attribute) {
            $productAttribute = $attribute->getProductAttribute();
            if ($productAttribute->getSourceModel()) {
                $label = $productAttribute->getSource()->getOptionText(
                    $row->getData($productAttribute->getAttributeCode())
                );
            } else {
                $label = $row->getData($productAttribute->getAttributeCode());
            }
            $item = [];
            $item['label'] = $label;
            $item['attribute_id'] = $productAttribute->getId();
            $item['value_index'] = $row->getData($productAttribute->getAttributeCode());
            $result[] = $item;
        }

        return $this->_jsonEncoder->encode($result);
    }
}
