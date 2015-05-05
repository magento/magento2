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
class Column extends AbstractComponent implements ColumnInterface
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
        return static::NAME . '.' . $this->getData('config/dataType');
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();

        $dataType = $this->getData('config/dataType');
        $wrappedComponentConfig = [];
        if ($dataType) {
            $this->wrappedComponent = $this->uiComponentFactory->create(
                $this->getName(),
                $dataType,
                array_merge(['context' => $this->getContext()], (array) $this->getData())
            );
            $this->wrappedComponent->prepare();
            $wrappedComponentConfig = $this->getConfiguration($this->wrappedComponent);
        }

        $this->applySorting();
        $jsConfig = array_replace_recursive($wrappedComponentConfig, $this->getConfiguration($this));
        $this->getContext()->addComponentDefinition($this->getComponentName(), $jsConfig);
    }

    /**
     * Get JS config
     *
     * @return array
     */
    public function getJsConfig()
    {
        if (isset($this->wrappedComponent)) {
            return array_replace_recursive(
                (array) $this->wrappedComponent->getData('config'),
                (array) $this->getData('config')
            );
        }

        return (array) $this->getData('config');
    }

    /**
     * To prepare items of a column
     *
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
        $sorting = $this->getContext()->getRequestParam('sorting');
        $isSortable = $this->getData('config/sortable');
        if ($isSortable !== false
            && !empty($sorting['field'])
            && !empty($sorting['direction'])
            && $sorting['field'] === $this->getName()
        ) {
            $this->getContext()->getDataProvider()->addOrder(
                $this->getName(),
                strtoupper($sorting['direction'])
            );
        }
    }
}
