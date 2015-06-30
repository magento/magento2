<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

use Magento\Framework\Object;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\DataSourceInterface;

/**
 * Abstract class AbstractComponent
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractComponent extends Object implements UiComponentInterface
{
    /**
     * Render context
     *
     * @var ContextInterface
     */
    protected $context;

    /**
     * @var UiComponentInterface[]
     */
    protected $components;

    /**
     * @var array
     */
    protected $componentData = [];

    /**
     * @var DataSourceInterface[]
     */
    protected $dataSources = [];

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        array $components = [],
        array $data = []
    ) {
        $this->context = $context;
        $this->components = $components;
        $this->_data = array_replace_recursive($this->_data, $data);
    }

    /**
     * Get component context
     *
     * @return ContextInterface
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Get component name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData('name');
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        $jsConfig = $this->getJsConfig($this);
        if (isset($jsConfig['provider'])) {
            unset($jsConfig['extends']);
            $this->getContext()->addComponentDefinition($this->getName(), $jsConfig);
        } else {
            $this->getContext()->addComponentDefinition($this->getComponentName(), $jsConfig);
        }
        if ($this->hasData('buttons')) {
            $this->getContext()->addButtons($this->getData('buttons'), $this);
        }
    }

    /**
     * Produce and return block's html output
     *
     * @return string
     */
    public function toHtml()
    {
        $this->render();
    }

    /**
     * Render component
     *
     * @return string
     */
    public function render()
    {
        $result = $this->getContext()->getRenderEngine()->render($this, $this->getTemplate());
        return $result;
    }

    /**
     * Add component
     *
     * @param string $name
     * @param UiComponentInterface $component
     * @return void
     */
    public function addComponent($name, UiComponentInterface $component)
    {
        $this->components[$name] = $component;
    }

    /**
     * @param string $name
     * @return UiComponentInterface
     */
    public function getComponent($name)
    {
        return isset($this->components[$name]) ? $this->components[$name] : null;
    }

    /**
     * Get components
     *
     * @return UiComponentInterface[]
     */
    public function getChildComponents()
    {
        return $this->components;
    }

    /**
     * Render child component
     *
     * @param string $name
     * @return string
     */
    public function renderChildComponent($name)
    {
        $result = null;
        if (isset($this->components[$name])) {
            $result = $this->components[$name]->render();
        }
        return $result;
    }

    /**
     * Get template
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->getData('template') . '.xhtml';
    }

    /**
     * Get component configuration
     *
     * @return array
     */
    public function getConfiguration()
    {
        return (array) $this->getData('config');
    }

    /**
     * Get configuration of related JavaScript Component
     * (force extending the root component if component does not extend other component)
     *
     * @param UiComponentInterface $component
     * @return array
     */
    public function getJsConfig(UiComponentInterface $component)
    {
        $jsConfig = (array) $component->getData('js_config');
        if (!isset($jsConfig['extends'])) {
            $jsConfig['extends'] = $component->getContext()->getNamespace();
        }
        return $jsConfig;
    }

    /**
     * Component data setter
     *
     * @param string|array $key
     * @param mixed $value
     * @return void
     */
    public function setData($key, $value = null)
    {
        parent::setData($key, $value);
    }

    /**
     * Component data getter
     *
     * @param string $key
     * @param string|int $index
     * @return mixed
     */
    public function getData($key = '', $index = null)
    {
        return parent::getData($key, $index);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function prepareDataSource(array & $dataSource)
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    public function getDataSourceData()
    {
        return [];
    }
}
