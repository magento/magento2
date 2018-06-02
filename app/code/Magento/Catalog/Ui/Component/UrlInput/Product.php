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
            'component' => 'Magento_Catalog/js/components/product-ui-select',
            'disableLabel' => true,
            'filterOptions' => true,
            'searchOptions' => true,
            'chipsEnabled' => true,
            'levelsVisibility' => '1',
            'options' => [],
            'sortOrder' => 25,
            'multiple' => false,
            'closeBtn' => true,
            'template' => 'ui/grid/filters/elements/ui-select',
            'searchUrl' => $this->urlBuilder->getUrl('catalog/product/search'),
            'filterPlaceholder' => __('Product Name or SKU'),
            'isDisplayEmptyPlaceholder' => true,
            'emptyOptionsHtml' => __('Start typing to find products'),
            'missingValuePlaceholder' => __('Product with ID: %s doesn\'t exist'),
            'isDisplayMissingValuePlaceholder' => true,
            'isRemoveSelectedIcon' => true,
            'validationUrl' => $this->urlBuilder->getUrl('catalog/product/getSelected'),
        ];
    }
}
