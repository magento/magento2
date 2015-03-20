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
     * @param array $rowData
     * @param array $rowActionConfig
     * @return array
     */
    public function getData(array $rowData, array $rowActionConfig = [])
    {
        return [
            'edit' => [
                'href' => $this->urlBuilder->getUrl(
                    isset($rowActionConfig['url_path']) ? $rowActionConfig['url_path'] : static::URL_PATH,
                    ['page_id' => $rowData['page_id']]
                ),
                'label' => __('Edit'),
                'hidden' => true,

            ],
            'preview' => [
                'href' => $this->actionUrlBuilder->getUrl(
                    $rowData['identifier'],
                    isset($rowData['_first_store_id']) ? $rowData['_first_store_id'] : null,
                    isset($rowData['store_code']) ? $rowData['store_code'] : null
                ),
                'label' => __('Preview'),
            ]
        ];
    }
}
