<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Filter\Type;

use Magento\Ui\Component\AbstractComponent;
use Magento\Ui\Component\Filter\DataProvider;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Range
 */
class Range extends AbstractComponent
{
    const NAME = 'filter_range';

    /**
     * @var DataProvider
     */
    protected $dataProvider;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param DataProvider $dataProvider
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        DataProvider $dataProvider,
        array $components = [],
        array $data = []
    ) {
        $this->dataProvider = $dataProvider;
        parent::__construct($context, $components, $data);
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
        $this->applyFilter();
        $jsConfig = $this->getJsConfiguration($this);
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
        $value = $value = $this->dataProvider->getData($this->getName());
        if (!empty($value['from']) || !empty($value['to'])) {
            if (isset($value['from']) && empty($value['from']) && $value['from'] !== '0') {
                $value['orig_from'] = $value['from'];
                $value['from'] = null;
            }
            if (isset($value['to']) && empty($value['to']) && $value['to'] !== '0') {
                $value['orig_to'] = $value['to'];
                $value['to'] = null;
            }
        } else {
            $value = null;
        }

        return $value;
    }
}
