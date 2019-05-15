<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Ui\Component\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\ImportExport\Controller\Adminhtml\Export\File\Download;
use Magento\ImportExport\Controller\Adminhtml\Export\File\Delete;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

/**
 * Actions for export grid.
 */
class ExportGridActions extends Column
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * ExportGridActions constructor.
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
                if (isset($item['file_name'])) {
                    $item[$name]['view'] = [
                        'href' => $this->urlBuilder->getUrl(Download::URL, ['filename' => $item['file_name']]),
                        'label' => __('Download')
                    ];
                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(Delete::URL, ['filename' => $item['file_name']]),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete'),
                            'message' => __('Are you sure you wan\'t to delete a file?')
                        ],
                        'post' => true,
                    ];
                }
            }
        }
        return $dataSource;
    }
}
