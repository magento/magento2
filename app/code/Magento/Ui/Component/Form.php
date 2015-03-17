<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

use Magento\Framework\View\Element\UiComponent\DataSourceInterface;

/**
 * Class Form
 */
class Form extends AbstractComponent
{
    const NAME = 'form';

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

        $jsConfig = $this->getJsConfiguration($this);
        unset($jsConfig['extends']);
        $this->getContext()->addComponentDefinition($this->getContext()->getNamespace(), $jsConfig);
    }

    /**
     * @return array
     */
    public function getDataSourceData()
    {
        $namespace = $this->getContext()->getNamespace();
        $dataSources = [];
        foreach ($this->getChildComponents() as $component) {
            if ($component instanceof DataSourceInterface) {
                $dataProvider = $component->getDataProvider();
                $id = $this->getContext()->getRequestParam($dataProvider->getRequestFieldName());
                if ($id) {
                    $dataProvider->addFilter($dataProvider->getPrimaryFieldName(), $id);
                    $preparedData = [];
                    $data = $dataProvider->getData();
                    if (!empty($data['items'])) {
                        $preparedData[$namespace] = $data['items'][0];
                    }
                } else {
                    $preparedData = [];
                }
                $dataSources[] = [
                    'type' => $component->getComponentName(),
                    'name' => $component->getName(),
                    'dataScope' => $component->getContext()->getNamespace(),
                    'config' => [
                        'data' => $preparedData
                    ]
                ];
            }
        }
        return $dataSources;
    }
}
