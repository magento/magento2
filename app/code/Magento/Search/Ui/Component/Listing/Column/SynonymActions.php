<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

/**
 * Class SynonymActions provides grid actions for synonyms
 */
class SynonymActions extends Column
{
    /** Url path */
    const SYNONYM_URL_PATH_DELETE = 'search/synonyms/delete';
    const SYNONYM_URL_PATH_EDIT = 'search/synonyms/edit';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
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
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                $item[$name]['delete'] = [
                    'href' => $this->urlBuilder->getUrl(
                        self::SYNONYM_URL_PATH_DELETE,
                        ['group_id' => $item['group_id']]
                    ),
                    'label' => __('Delete'),
                    'confirm' => [
                        'title' => __('Delete'),
                        'message' => __('Are you sure you want to delete synonym group with id: %1?', $item['group_id'])
                    ],
                    'post' => true
                ];
                $item[$name]['edit'] = [
                    'href' => $this->urlBuilder->getUrl(self::SYNONYM_URL_PATH_EDIT, ['group_id' => $item['group_id']]),
                    'label' => __('View/Edit'),
                ];
            }
        }

        return $dataSource;
    }
}
