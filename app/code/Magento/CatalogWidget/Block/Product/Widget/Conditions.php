<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product Chooser for "Product Link" Cms Widget Plugin
 */
namespace Magento\CatalogWidget\Block\Product\Widget;

use Magento\Backend\Block\Template;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

/**
 * Class Conditions
 * @since 2.0.0
 */
class Conditions extends Template implements RendererInterface
{
    /**
     * @var \Magento\Rule\Block\Conditions
     * @since 2.0.0
     */
    protected $conditions;

    /**
     * @var \Magento\CatalogWidget\Model\Rule
     * @since 2.0.0
     */
    protected $rule;

    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     * @since 2.0.0
     */
    protected $elementFactory;

    /**
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $registry;

    /**
     * @var AbstractElement
     * @since 2.0.0
     */
    protected $element;

    /**
     * @var \Magento\Framework\Data\Form\Element\Text
     * @since 2.0.0
     */
    protected $input;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'product/widget/conditions.phtml';

    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $elementFactory
     * @param \Magento\Rule\Block\Conditions $conditions
     * @param \Magento\CatalogWidget\Model\Rule $rule
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\CatalogWidget\Model\Rule $rule,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->elementFactory = $elementFactory;
        $this->conditions = $conditions;
        $this->rule = $rule;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function _construct()
    {
        $widgetParameters = [];
        $widget = $this->registry->registry('current_widget_instance');
        if ($widget) {
            $widgetParameters = $widget->getWidgetParameters();
        } elseif ($widgetOptions = $this->getLayout()->getBlock('wysiwyg_widget.options')) {
            $widgetParameters = $widgetOptions->getWidgetValues();
        }

        if (isset($widgetParameters['conditions'])) {
            $this->rule->loadPost($widgetParameters);
        }
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function render(AbstractElement $element)
    {
        $this->element = $element;
        $this->rule->getConditions()->setJsFormObject($this->getHtmlId());
        return $this->toHtml();
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getNewChildUrl()
    {
        return $this->getUrl(
            'catalog_widget/product_widget/conditions/form/' . $this->getElement()->getContainer()->getHtmlId()
        );
    }

    /**
     * @return AbstractElement
     * @since 2.0.0
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getHtmlId()
    {
        return $this->getElement()->getContainer()->getHtmlId();
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getInputHtml()
    {
        $this->input = $this->elementFactory->create('text');
        $this->input->setRule($this->rule)->setRenderer($this->conditions);
        return $this->input->toHtml();
    }
}
