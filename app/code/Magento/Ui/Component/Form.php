<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * @api
 * @since 100.0.2
 */
class Form extends AbstractComponent
{
    const NAME = 'form';

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @param ContextInterface $context
     * @param FilterBuilder $filterBuilder
     * @param UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        FilterBuilder $filterBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->filterBuilder = $filterBuilder;
        parent::__construct(
            $context,
            $components,
            $data
        );
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
     * {@inheritdoc}
     */
    public function getDataSourceData()
    {
        $dataSource = [];

        $id = $this->getContext()->getRequestParam($this->getContext()->getDataProvider()->getRequestFieldName(), null);
        $filter = $this->filterBuilder->setField($this->getContext()->getDataProvider()->getPrimaryFieldName())
            ->setValue($id)
            ->create();
        $this->getContext()->getDataProvider()
            ->addFilter($filter);

        $data = $this->getContext()->getDataProvider()->getData();

        if (isset($data[$id])) {
            $dataSource = [
                'data' => $data[$id]
            ];
        } elseif (isset($data['items'])) {
            foreach ($data['items'] as $item) {
                if ($item[$item['id_field_name']] == $id) {
                    $dataSource = ['data' => ['general' => $item]];
                }
            }
        }
        return $dataSource;
    }
}
