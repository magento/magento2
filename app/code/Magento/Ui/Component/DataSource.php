<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\DataSourceInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;

/**
 * Class DataSource
 */
class DataSource extends AbstractComponent implements DataSourceInterface
{
    const NAME = 'dataSource';

    /**
     * @var DataProviderInterface
     */
    protected $dataProvider;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param DataProviderInterface $dataProvider
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        DataProviderInterface $dataProvider,
        array $components = [],
        array $data = []
    ) {
        $this->dataProvider = $dataProvider;
        $context->setDataProvider($dataProvider);
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
        $jsConfig = $this->getJsConfig($this);
        unset($jsConfig['extends']);
        $this->getContext()->addComponentDefinition($this->getComponentName(), $jsConfig);
    }

    /**
     * @return DataProviderInterface
     */
    public function getDataProvider()
    {
        return $this->dataProvider;
    }
}
