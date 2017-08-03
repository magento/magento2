<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\DataSourceInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;

/**
 * @api
 * @since 2.0.0
 */
class DataSource extends AbstractComponent implements DataSourceInterface
{
    const NAME = 'dataSource';

    /**
     * @var DataProviderInterface
     * @since 2.0.0
     */
    protected $dataProvider;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param DataProviderInterface $dataProvider
     * @param array $components
     * @param array $data
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * Prepare component configuration
     *
     * @return void
     * @since 2.0.0
     */
    public function prepare()
    {
        $jsConfig = $this->getJsConfig($this);
        unset($jsConfig['extends']);
        $this->getContext()->addComponentDefinition($this->getComponentName(), $jsConfig);
    }

    /**
     * @return DataProviderInterface
     * @since 2.0.0
     */
    public function getDataProvider()
    {
        return $this->dataProvider;
    }
}
