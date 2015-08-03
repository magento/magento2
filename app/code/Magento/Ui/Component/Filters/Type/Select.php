<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Filters\Type;

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
     * @param UiComponentFactory $uiComponentFactory
     * @param OptionSourceInterface $optionsProvider
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OptionSourceInterface $optionsProvider = null,
        array $components = [],
        array $data = []
    ) {
        $this->optionsProvider = $optionsProvider;
        parent::__construct($context, $uiComponentFactory, $components, $data);
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
        $this->wrappedComponent = $this->uiComponentFactory->create(
            $this->getName(),
            static::COMPONENT,
            ['context' => $this->getContext(), 'options' => $this->optionsProvider]
        );
        $this->wrappedComponent->prepare();
        // Merge JS configuration with wrapped component configuration
        $jsConfig = array_replace_recursive(
            $this->getJsConfig($this->wrappedComponent),
            $this->getJsConfig($this)
        );
        $this->setData('js_config', $jsConfig);

        $this->setData(
            'config',
            array_replace_recursive(
                (array)$this->wrappedComponent->getData('config'),
                (array)$this->getData('config')
            )
        );

        $this->applyFilter();

        parent::prepare();
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
            $this->getContext()->getDataProvider()->addFilter($condition, $this->getName());
        }
    }

    /**
     * Get condition by data type
     *
     * @return array|null
     */
    public function getCondition()
    {
        $value = isset($this->filterData[$this->getName()]) ? $this->filterData[$this->getName()] : null;
        $condition = null;
        if (!empty($value) || is_numeric($value)) {
            $condition = ['eq' => $value];
        }

        return $condition;
    }
}
