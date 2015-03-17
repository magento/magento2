<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Filter\Type;

use Magento\Ui\Component\Filter\AbstractFilter;
use Magento\Ui\Component\Form\Element\Input as ElementInput;

/**
 * Class Input
 */
class Input extends AbstractFilter
{
    const NAME = 'filter_input';

    const COMPONENT = 'input';

    /**
     * Wrapped component
     *
     * @var ElementInput
     */
    protected $wrappedComponent;

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
            ['context' => $this->getContext()]
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
            $condition = ['like' => sprintf('%%%s%%', $value)];
        }

        return $condition;
    }
}
