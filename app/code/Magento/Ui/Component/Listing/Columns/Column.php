<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\AbstractComponent;

/**
 * @api
 * @since 100.0.2
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepare()
    {
        $this->addFieldToSelect();

        $dataType = $this->getData('config/dataType');
        if ($dataType) {
            $this->wrappedComponent = $this->uiComponentFactory->create(
                $this->getName(),
                $dataType,
                array_merge(['context' => $this->getContext()], (array) $this->getData())
            );
            $this->wrappedComponent->prepare();
            $wrappedComponentConfig = $this->getJsConfig($this->wrappedComponent);
            // Merge JS configuration with wrapped component configuration
            $jsConfig = array_replace_recursive($wrappedComponentConfig, $this->getJsConfig($this));
            $this->setData('js_config', $jsConfig);

            $this->setData(
                'config',
                array_replace_recursive(
                    (array)$this->wrappedComponent->getData('config'),
                    (array)$this->getData('config')
                )
            );
        }

        $this->applySorting();

        parent::prepare();
    }

    /**
     * Prepares items of a column
     *
     * @param array $items
     * @return array
     */
    public function prepareItems(array &$items)
    {
        return $items;
    }

    /**
     * Adds additional field to select object
     *
     * @return void
     */
    protected function addFieldToSelect()
    {
        if ($this->getData('config/add_field')) {
            $this->getContext()->getDataProvider()->addField($this->getName());
        }
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
            && in_array(strtoupper($sorting['direction']), ['ASC', 'DESC'], true)
        ) {
            $this->getContext()->getDataProvider()->addOrder(
                $this->getName(),
                strtoupper($sorting['direction'])
            );
        }
    }
}
