<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\MassAction\Columns;

use Magento\Ui\Component\AbstractComponent;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\Listing\Columns\ColumnInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Column
 * @since 2.0.0
 */
class Column extends AbstractComponent implements ColumnInterface
{
    const NAME = 'column.massaction';

    /**
     * Wrapped component
     *
     * @var UiComponentInterface
     * @since 2.0.0
     */
    protected $wrappedComponent;

    /**
     * UI component factory
     *
     * @var UiComponentFactory
     * @since 2.0.0
     */
    protected $uiComponentFactory;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * Prepare items
     *
     * @param array $items
     * @return array
     * @since 2.0.0
     */
    public function prepareItems(array & $items)
    {
        return $items;
    }
}
