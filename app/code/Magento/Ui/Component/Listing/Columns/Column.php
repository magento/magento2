<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Listing\Columns;

use Magento\Ui\Component\AbstractComponent;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Column
 */
class Column extends AbstractComponent
{
    const NAME = 'column';

    /**
     * Wrapped component
     *
     * @var UiComponentInterface
     */
    protected $wrappedComponent;

    /**
     * UI component factory
     *
     * @var UiComponentFactory
     */
    protected $uiComponentFactory;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->uiComponentFactory = $uiComponentFactory;
        parent::__construct($context, $components, $data);
    }

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME . '.' . $this->getData('dataType');
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();

        $dataType = $this->getData('dataType');
        $wrappedComponentConfig = [];
        if ($dataType) {
            $this->wrappedComponent = $this->uiComponentFactory->create(
                $this->getName(),
                $dataType,
                array_merge(['context' => $this->getContext()], (array) $this->getData())
            );
            $this->wrappedComponent->prepare();
            $wrappedComponentConfig = $this->getJsConfiguration($this->wrappedComponent);
        }

        $this->applySorting();
        $jsConfig = array_replace_recursive($wrappedComponentConfig, $this->getJsConfiguration($this));
        $this->getContext()->addComponentDefinition($this->getComponentName(), $jsConfig);
    }

    /**
     * Get JS config
     *
     * @return array
     */
    public function getJsConfig()
    {
        return array_replace_recursive(
            (array) $this->wrappedComponent->getData('config'),
            (array) $this->getData('config')
        );
    }

    /**
     * @param array $items
     * @return array
     */
    public function prepareItems(array & $items)
    {
        return $items;
    }

    /**
     * Apply sorting
     *
     * @return void
     */
    protected function applySorting()
    {
        $direction = $this->getContext()->getRequestParam('dir', $this->getData('config/sorting'));
        if (!empty($direction) ) {
            $this->getContext()->getDataProvider()->addOrder(
                $this->getName(),
                strtoupper($direction)
            );
        }
    }
}
