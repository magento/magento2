<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Product\CopyConstructor;

class Downloadable implements \Magento\Catalog\Model\Product\CopyConstructorInterface
{
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(\Magento\Framework\Json\Helper\Data $jsonHelper)
    {
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * Duplicating downloadable product data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Product $duplicate
     * @return void
     */
    public function build(\Magento\Catalog\Model\Product $product, \Magento\Catalog\Model\Product $duplicate)
    {
        if ($product->getTypeId() !== \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
            //do nothing if not downloadable
            return;
        }
        $data = [];
        /** @var \Magento\Downloadable\Model\Product\Type $type */
        $type = $product->getTypeInstance();
        foreach ($type->getLinks($product) as $link) {
            /* @var \Magento\Downloadable\Model\Link $link */
            $linkData = $link->getData();
            $data['link'][] = [
                'is_delete' => false,
                'link_id' => null,
                'title' => $linkData['title'],
                'is_shareable' => $linkData['is_shareable'],
                'sample' => [
                    'type' => $linkData['sample_type'],
                    'url' => $linkData['sample_url'],
                    'file' => [
                        [
                            'file' => $linkData['sample_file'],
                            'name' => $linkData['sample_file'],
                            'size' => 0,
                            'status' => null,
                        ],
                    ],
                ],
                'file' => [
                    [
                        'file' => $linkData['link_file'],
                        'name' => $linkData['link_file'],
                        'size' => 0,
                        'status' => null,
                    ],
                ],
                'type' => $linkData['link_type'],
                'link_url' => $linkData['link_url'],
                'sort_order' => $linkData['sort_order'],
                'number_of_downloads' => $linkData['number_of_downloads'],
                'price' => $linkData['price'],
            ];
        }

        /** @var \Magento\Downloadable\Model\Sample $sample */
        foreach ($type->getSamples($product) as $sample) {
            $sampleData = $sample->getData();
            $data['sample'][] = [
                'is_delete' => false,
                'sample_id' => null,
                'title' => $sampleData['title'],
                'type' => $sampleData['sample_type'],
                'file' => [
                    [
                        'file' => $sampleData['sample_file'],
                        'name' => $sampleData['sample_file'],
                        'size' => 0,
                        'status' => null,
                    ],
                ],
                'sample_url' => $sampleData['sample_url'],
                'sort_order' => $sampleData['sort_order'],
            ];
        }
        $duplicate->setDownloadableData($data);
    }
}
