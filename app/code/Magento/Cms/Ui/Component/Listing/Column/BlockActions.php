<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;

/**
 * Class BlockActions
 */
class BlockActions extends Column
{
    /**
     * Url path
     */
    const URL_PATH_EDIT = 'cms/block/edit';
    const URL_PATH_DELETE = 'cms/block/delete';
    const URL_PATH_DETAILS = 'cms/block/details';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Escaper
     */
    private $escaper;

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
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['block_id'])) {
                    $title = $this->getEscaper()->escapeHtml($item['title']);
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'block_id' => $item['block_id']
                                ]
                            ),
                            'label' => __('Edit')
                        ],
                        'delete' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    'block_id' => $item['block_id']
                                ]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __('Delete %1', $title),
                                'message' => __('Are you sure you wan\'t to delete a %1 record?', $title)
                            ]
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }

    /**
     * Get instance of escaper
     * @return Escaper
     * @deprecated
     */
    private function getEscaper()
    {
        if (!$this->escaper) {
            $this->escaper = ObjectManager::getInstance()->get(Escaper::class);
        }
        return $this->escaper;
    }
}
