<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Filters\Type;

use Magento\Ui\Component\AbstractComponent;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\Api\FilterBuilder;

/**
 * Abstract class AbstractFilter
 */
abstract class AbstractFilter extends AbstractComponent
{
    /**
     * Component name
     */
    const NAME = 'filter';

    /**
     * Filter variable name
     */
    const FILTER_VAR = 'filters';

    /**
     * Filter modifier variable name
     */
    const FILTER_MODIFIER = 'filters_modifier';

    /**
     * Filter data
     *
     * @var array
     */
    protected $filterData;

    /**
     * @var UiComponentFactory
     */
    protected $uiComponentFactory;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param FilterBuilder $filterBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        FilterBuilder $filterBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->uiComponentFactory = $uiComponentFactory;
        $this->filterBuilder = $filterBuilder;
        parent::__construct($context, $components, $data);
        $this->filterData = $this->getContext()->getFiltersParams();
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
     * {@inheritdoc}
     */
    public function prepare()
    {
        $this->applyFilterModifier();
        parent::prepare();
    }

    /**
     * Apply modifiers for filters
     */
    protected function applyFilterModifier()
    {
        $filterModifier = $this->getContext()->getRequestParam(self::FILTER_MODIFIER);
        if (isset($filterModifier[$this->getName()]['condition_type'])) {
            $conditionType = $filterModifier[$this->getName()]['condition_type'];
            switch ($conditionType) {
                case 'notnull':
                    $filter = $this->filterBuilder->setConditionType($conditionType)
                        ->setField($this->getName())
                        ->create();
                    $this->getContext()->getDataProvider()->addFilter($filter);
                    break;
                case 'neq':
                    $value = isset($filterModifier[$this->getName()]['value'])
                        ? $filterModifier[$this->getName()]['value']
                        : null;
                    $filter = $this->filterBuilder->setConditionType($conditionType)
                        ->setField($this->getName())
                        ->setValue($value)
                        ->create();
                    $this->getContext()->getDataProvider()->addFilter($filter);
                    break;
            }
        }
    }
}
