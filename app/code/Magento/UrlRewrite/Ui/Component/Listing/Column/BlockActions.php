<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class EditAction
 */
class BlockActions extends Column
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['url_rewrite_id'])) {
                    $editUrlPath = $this->getData('config/editUrlPath') ?: '#';
                    $deleteUrlPath = $this->getData('config/deleteUrlPath') ?: '#';
                    $urlEntityParamName = $this->getData('config/urlEntityParamName') ?: 'url_rewrite_id';
                    $title = $item['url_rewrite_id'];
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                $editUrlPath,
                                [
                                    $urlEntityParamName => $item['url_rewrite_id']
                                ]
                            ),
                            'label' => __('Edit')
                        ],
                        'delete' => [
                            'href' => $this->urlBuilder->getUrl(
                                $deleteUrlPath,
                                [
                                    $urlEntityParamName => $item['url_rewrite_id']
                                ]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __('Delete record #%1', $title),
                                'message' => __('Are you sure you want to delete a record #%1?', $title)
                            ],
                            'post' => true
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
