<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Massaction;

/**
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @deprecated
 */
class Additional extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Framework\View\Layout\Argument\Interpreter\Options
     */
    protected $_optionsInterpreter;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\View\Layout\Argument\Interpreter\Options $optionsInterpreter
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\View\Layout\Argument\Interpreter\Options $optionsInterpreter,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_optionsInterpreter = $optionsInterpreter;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        foreach ($this->getData('fields') as $itemId => $item) {
            $this->_prepareFormItem($item);
            $form->addField($itemId, $item['type'], $item);
        }
        $this->setForm($form);
        return $this;
    }

    /**
     * Prepare form item
     *
     * @param array &$item
     * @return void
     */
    protected function _prepareFormItem(array &$item)
    {
        if ($item['type'] == 'select' && is_string($item['values'])) {
            $modelClass = $item['values'];
            $item['values'] = $this->_optionsInterpreter->evaluate(['model' => $modelClass]);
        }
        $item['class'] = isset($item['class']) ? $item['class'] . ' absolute-advice' : 'absolute-advice';
    }
}
