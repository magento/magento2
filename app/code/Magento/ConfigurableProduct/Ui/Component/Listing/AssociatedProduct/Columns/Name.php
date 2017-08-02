<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Ui\Component\Listing\AssociatedProduct\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class \Magento\ConfigurableProduct\Ui\Component\Listing\AssociatedProduct\Columns\Name
 *
 * @since 2.1.0
 */
class Name extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Column name
     */
    const NAME = 'column.name';

    /**
     * @var UrlInterface
     * @since 2.1.0
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     * @since 2.1.0
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     * @since 2.1.0
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$fieldName]) && isset($item['entity_id'])) {
                    $url = $this->urlBuilder->getUrl('catalog/product/edit', ['id' => $item['entity_id']]);
                    $item['product_link'] = '<a href="' . $url . '" target="_blank">' . $item[$fieldName] . '</a>';
                }
            }
        }

        return $dataSource;
    }
}
