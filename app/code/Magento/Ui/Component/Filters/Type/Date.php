<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Filters\Type;

use Magento\Ui\Component\Form\Element\DataType\Date as DataTypeDate;

/**
 * Class Date
 */
class Date extends AbstractFilter
{
    const NAME = 'filter_date';

    const COMPONENT = 'date';

    /**
     * Wrapped component
     *
     * @var DataTypeDate
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
            $this->getConfiguration($this->wrappedComponent),
            $this->getConfiguration($this)
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
     * Get condition
     *
     * @return array|null
     */
    protected function getCondition()
    {
        $value = isset($this->filterData[$this->getName()]) ? $this->filterData[$this->getName()] : null;
        if (!empty($value['from']) || !empty($value['to'])) {
            if (!empty($value['from'])) {
                $value['orig_from'] = $value['from'];
                $value['from'] = $this->wrappedComponent->convertDate(
                    $value['from'],
                    $this->wrappedComponent->getLocale()
                );
            } else {
                unset($value['from']);
            }
            if (!empty($value['to'])) {
                $value['orig_to'] = $value['to'];
                $value['to'] = $this->wrappedComponent->convertDate(
                    $value['to'],
                    $this->wrappedComponent->getLocale()
                );
            } else {
                unset($value['to']);
            }
            $value['datetime'] = true;
            $value['locale'] = $this->wrappedComponent->getLocale();
        } else {
            $value = null;
        }

        return $value;
    }
}
