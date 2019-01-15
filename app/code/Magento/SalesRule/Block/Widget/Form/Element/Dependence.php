<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Block\Widget\Form\Element;

/**
 * Form element dependencies mapper
 * Assumes that one element may depend on other element values.
 * Will toggle as "enabled" only if all elements it depends from toggle as true.
 */
class Dependence extends \Magento\Backend\Block\Widget\Form\Element\Dependence
{
    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory $fieldFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory $fieldFactory,
        array $data = []
    ) {
        parent::__construct($context, $jsonEncoder, $fieldFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if (!$this->_depends) {
            return '';
        }

        return '<script>
            require(["uiRegistry", "mage/adminhtml/form"], function(registry) {
        var controller = new FormElementDependenceController(' .
            $this->_getDependsJson() .
            ($this->_configOptions ? ', ' .
            $this->_jsonEncoder->encode(
                $this->_configOptions
            ) : '') . ');
            registry.set("formDependenceController", controller);
            });</script>';
    }
}
