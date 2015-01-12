<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Ui\DataProvider\Page\Row;

use Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Listing\RowInterface;

/**
 * Class Actions
 */
class Actions implements RowInterface
{
    /**
     * Url path
     */
    const URL_PATH = 'cms/page/edit';

    /**
     * @var UrlBuilder
     */
    protected $actionUrlBuilder;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor
     *
     * @param UrlBuilder $actionUrlBuilder
     * @param UrlInterface $urlBuilder
     */
    public function __construct(UrlBuilder $actionUrlBuilder, UrlInterface $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
        $this->actionUrlBuilder = $actionUrlBuilder;
    }

    /**
     * Get data
     *
     * @param array $dataRow
     * @return mixed
     */
    public function getData(array $dataRow)
    {
        return [
            'edit' => [
                'href' => $this->urlBuilder->getUrl(static::URL_PATH, ['page_id' => $dataRow['page_id']]),
                'label' => __('Edit'),
                'hidden' => true,

            ],
            'preview' => [
                'href' => $this->actionUrlBuilder->getUrl(
                    $dataRow['identifier'],
                    isset($dataRow['_first_store_id']) ? $dataRow['_first_store_id'] : null,
                    isset($dataRow['store_code']) ? $dataRow['store_code'] : null
                ),
                'label' => __('Preview'),
            ]
        ];
    }
}
