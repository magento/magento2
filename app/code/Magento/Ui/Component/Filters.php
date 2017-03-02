<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\ObserverInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\Listing\Columns\ColumnInterface;

/**
 * Class Filters
 */
class Filters extends AbstractComponent implements ObserverInterface
{
    const NAME = 'filters';

    /**
     * Filters created from columns
     *
     * @var UiComponentInterface[]
     */
    protected $columnFilters = [];

    /**
     * Maps filter declaration to type
     *
     * @var array
     */
    protected $filterMap = [
        'text' => 'filterInput',
        'textRange' => 'filterRange',
        'select' => 'filterSelect',
        'dateRange' => 'filterDate',
    ];

    /**
     * @var UiComponentFactory
     */
    protected $uiComponentFactory;

    /**
     * @inheritDoc
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->uiComponentFactory = $uiComponentFactory;
    }


    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * @inheritDoc
     */
    public function update(UiComponentInterface $component)
    {
        if ($component instanceof ColumnInterface) {
            $filterType = $component->getData('config/filter');

            if (is_array($filterType)) {
                $filterType = $filterType['filterType'];
            }

            if (!$filterType) {
                return;
            }

            if (isset($this->filterMap[$filterType])) {
                $filterComponent = $this->uiComponentFactory->create(
                    $component->getName(),
                    $this->filterMap[$filterType],
                    ['context' => $this->getContext()]
                );
                $filterComponent->setData('config', $component->getConfiguration());
                $filterComponent->prepare();
                $this->addComponent($component->getName(), $filterComponent);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function addComponent($name, UiComponentInterface $component)
    {
        $this->columnFilters[$name] = $component;
        parent::addComponent($name, $component);
    }

    /**
     * @inheritDoc
     */
    public function getChildComponents()
    {
        $result = parent::getChildComponents();
        foreach (array_keys($this->columnFilters) as $componentName) {
            if ($this->getComponent($componentName)) {
                unset($result[$componentName]);
            }
        }

        return $result;
    }
}
