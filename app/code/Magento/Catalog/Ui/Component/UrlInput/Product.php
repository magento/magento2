<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Ui\Component\UrlInput;

use Magento\Framework\UrlInterface;


class Product implements \Magento\Ui\Model\UrlInput\ConfigInterface
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * Product constructor.
     * @param UrlInterface $urlBuilder
     */
    public function __construct(UrlInterface $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig(): array
    {
        return [
            'label' => __('Product'),
            'component' => 'Magento_Ui/js/form/element/ui-select',
            'disableLabel' => true,
            'filterOptions' => true,
            'searchOptions' => true,
            'chipsEnabled' => true,
            'levelsVisibility' => '1',
            'options' => [],
            'sortOrder' => 20,
            'multiple' => false,
            'closeBtn' => true,
            'template' => 'ui/grid/filters/elements/ui-select',
            'requestUrl' => $this->urlBuilder->getUrl('catalog/product/search'),
            'searchPlaceholder' => __('Product Name or SKU'),
            'isDisplayEmptyPlaceholder' => true,
            'emptyOptionsHtml' => __('Start typing to find products')
        ];
    }
}
