<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\DataProvider\Product\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\UrlInterface;

/**
 * DataProvider for new category form
 *
 * @api
 * @since 2.1.0
 */
class NewCategoryDataProvider extends AbstractDataProvider
{
    /**
     * @var UrlInterface
     * @since 2.1.0
     */
    protected $urlBuilder;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param UrlInterface $urlBuilder
     * @param array $meta
     * @param array $data
     * @since 2.1.0
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        UrlInterface $urlBuilder,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getData()
    {
        $this->data = array_replace_recursive(
            $this->data,
            [
                'config' => [
                    'data' => [
                        'is_active' => 1,
                        'include_in_menu' => 1,
                        'return_session_messages_only' => 1,
                        'use_config' => ['available_sort_by', 'default_sort_by']
                    ]
                ]
            ]
        );

        return $this->data;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getMeta()
    {
        $this->meta = [
            'data' => [
                'children' => [
                    'parent' => [
                        'notice' => $this->getNotice(),
                    ]
                ]
            ]
        ];

        return parent::getMeta();
    }

    /**
     * Get notice message
     *
     * @return \Magento\Framework\Phrase
     * @since 2.1.0
     */
    protected function getNotice()
    {
        return __(
            'If there are no custom parent categories, please use the default parent category.'
            . ' You can reassign the category at any time in'
            . ' <a href="%1" target="_blank">Products &gt; Categories</a>.',
            $this->urlBuilder->getUrl('catalog/category')
        );
    }
}
