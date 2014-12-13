<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Cms\Ui\DataProvider\Block\Row;

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
    const URL_PATH = 'cms/block/edit';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param UrlInterface $urlBuilder
     */
    public function __construct(UrlInterface $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
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
                'href' => $this->urlBuilder->getUrl(static::URL_PATH, ['block_id' => $dataRow['block_id']]),
                'label' => __('Edit'),
            ]
        ];
    }
}
