<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Component;

/**
 * Class Paging
 *
 * @api
 * @since 100.0.2
 */
class Paging extends AbstractComponent
{
    const NAME = 'paging';

    /**
     * Default paging options
     *
     * @var array
     */
    private $defaultOptions = [
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
    ];

    /**
     * Default page size
     *
     * @var int
     */
    private $defaultPageSize = 20;

    /**
     * Default component data
     *
     * @var array
     */
    protected $_data = [
        'config' => [
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
        $this->preparePageSize();
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
        if (!isset($config['options'])) {
            $config['options'] = $this->defaultOptions;
        }
        foreach ($config['options'] as &$item) {
            $item['value'] = (int)$item['value'];
        }
        unset($item);
        $this->setData('config', $config);
    }

    /**
     * Prepare page size
     *
     * @return void
     */
    private function preparePageSize()
    {
        $config = $this->getData('config');
        if (!isset($config['pageSize'])) {
            $config['pageSize'] = $this->defaultPageSize;
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
        return (int)(isset($paging['current']) ? $paging['current'] : $defaultPage);
    }

    /**
     * Get size
     *
     * @param array|null $paging
     * @return int
     */
    protected function getSize($paging)
    {
        return (int)(isset($paging['pageSize']) ? $paging['pageSize'] : $this->getData('config/pageSize'));
    }
}
