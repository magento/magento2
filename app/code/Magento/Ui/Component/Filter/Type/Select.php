<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Filter\Type;

use Magento\Ui\Component\Filter\DataProvider;
use Magento\Ui\Component\Filter\AbstractFilter;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Element\Select as ElementSelect;

/**
 * Class Select
 */
class Select extends AbstractFilter
{
    const NAME = 'filter_select';

    const COMPONENT = 'select';

    /**
     * Wrapped component
     *
     * @var ElementSelect
     */
    protected $wrappedComponent;

    /**
     * @var OptionSourceInterface
     */
    protected $optionsProvider;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param DataProvider $dataProvider
     * @param UiComponentFactory $uiComponentFactory
     * @param OptionSourceInterface $optionsProvider
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        DataProvider $dataProvider,
        UiComponentFactory $uiComponentFactory,
        OptionSourceInterface $optionsProvider = null,
        array $components = [],
        array $data = []
    ) {
        $this->optionsProvider = $optionsProvider;
        parent::__construct($context, $dataProvider, $uiComponentFactory, $components, $data);
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
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();
        $this->wrappedComponent = $this->uiComponentFactory->create(
            $this->getName(),
            static::COMPONENT,
            ['context' => $this->getContext(), 'options' => $this->optionsProvider]
        );
        $this->wrappedComponent->prepare();

        $this->applyFilter();
        $jsConfig = array_replace_recursive(
            $this->getJsConfiguration($this->wrappedComponent),
            $this->getJsConfiguration($this)
        );
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
     * Get condition by data type
     *
     * @return array|null
     */
    public function getCondition()
    {
        $value = $this->dataProvider->getData($this->getName());
        $condition = null;
        if (!empty($value) || is_numeric($value)) {
            $condition = ['eq' => $value];
        }

        return $condition;
    }

    /**
     * Apply filter
     *
     * @return void
     */
    protected function applyFilter()
    {
        $condition = $this->getCondition();
        if ($condition !== null) {
            $this->getContext()->getDataProvider()->addFilter($this->getName(), $condition);
        }
    }
}
