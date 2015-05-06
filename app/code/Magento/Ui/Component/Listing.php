<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

use Magento\Ui\Component\Listing\Columns;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\DataSourceInterface;

/**
 * Class Listing
 */
class Listing extends AbstractComponent
{
    const NAME = 'listing';

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
     * Register component and it's page main actions
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();

        $jsConfig = $this->getConfiguration($this);
        unset($jsConfig['extends']);
        $this->getContext()->addComponentDefinition($this->getContext()->getNamespace(), $jsConfig);

        $this->getContext()->addButtons($this->getData('buttons'), $this);
    }

    /**
     * @inheritdoc
     */
    public function getDataSourceData()
    {
        $columns = $this->collectColumns();
        $dataSources = [];
        foreach ($this->getChildComponents() as $component) {
            // we need to process only Data Sources
            if (!$component instanceof DataSourceInterface) {
                continue;
            }
            $data = $component->getDataProvider()->getData();
            if (!empty($data['items']) && !empty($columns)) {
                // Columns may need to pre-process data before using it
                foreach ($columns as $column) {
                    $column->prepareItems($data['items']);
                }
            }

            $dataSources[] = [
                'type' => $component->getComponentName(),
                'name' => $component->getName(),
                'dataScope' => $component->getContext()->getNamespace(),
                'config' => array_replace_recursive(
                    [
                        'data' => $data,
                        'totalCount' => $component->getDataProvider()->count(),
                    ],
                    (array) $component->getData('config'),
                    // ensure that namespace hasn't been overridden by accident
                    [
                        'params' => [
                            'namespace' => $this->getContext()->getNamespace()
                        ],
                    ]
                ),
            ];
        }
        return $dataSources;
    }

    /**
     * Go through child components and collect Column types only.
     *
     * @return Column[]
     */
    protected function collectColumns()
    {
        $columns = [];
        foreach ($this->getChildComponents() as $component) {
            if ($component instanceof Columns) {
                foreach ($component->getChildComponents() as $column) {
                    if ($column instanceof Column) {
                        $columns[] = $column;
                    }
                }
            }
        }
        return $columns;
    }
}
