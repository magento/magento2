<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

/**
 * @api
 * @since 100.0.2
 */
class Paging extends AbstractComponent
{
    const NAME = 'paging';

    /**
     * Default component data
     *
     * @var array
     */
    protected $_data = [
        'config' => [
            'options' => [
                '20' => [
                    'value' => 20,
                    'label' => 20
                ],
                '30' => [
                    'value' => 30,
                    'label' => 30
                ],
                '50' => [
                    'value' => 50,
                    'label' => 50
                ],
                '100' => [
                    'value' => 100,
                    'label' => 100
                ],
                '200' => [
                    'value' => 200,
                    'label' => 200
                ],
            ],
            'pageSize' => 20,
            'current' => 1
        ]
    ];

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
     * Register component and apply paging settings to Data Provider
     *
     * @return void
     */
    public function prepare()
    {
        $this->prepareOptions();
        $paging = $this->getContext()->getRequestParam('paging');
        if (!isset($paging['notLimits'])) {
            $this->getContext()
                ->getDataProvider()
                ->setLimit($this->getOffset($paging), $this->getSize($paging));
        }

        parent::prepare();
    }

    /**
     * Prepare paging options
     *
     * @return void
     */
    protected function prepareOptions()
    {
        $config = $this->getData('config');
        if (isset($config['options'])) {
            $config['options'] = array_values($config['options']);
            foreach ($config['options'] as &$item) {
                $item['value'] = (int) $item['value'];
            }
            unset($item);
            $this->setData('config', $config);
        }
    }

    /**
     * Get offset
     *
     * @param array|null $paging
     * @return int
     */
    protected function getOffset($paging)
    {
        $defaultPage = $this->getData('config/current') ?: 1;
        return (int) (isset($paging['current']) ? $paging['current'] : $defaultPage);
    }

    /**
     * Get size
     *
     * @param array|null $paging
     * @return int
     */
    protected function getSize($paging)
    {
        $defaultLimit = $this->getData('config/pageSize') ?: 20;
        return (int) (isset($paging['pageSize']) ? $paging['pageSize'] : $defaultLimit);
    }
}
