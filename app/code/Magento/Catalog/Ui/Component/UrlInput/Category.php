<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\Component\UrlInput;

use Magento\Ui\Model\UrlInput\ConfigInterface;

/**
 * Returns configuration for category Url Input type
 */
class Category implements ConfigInterface
{
    /**
     * @var \Magento\Catalog\Ui\Component\Product\Form\Categories\Options
     */
    private $options;

    /**
     * @param \Magento\Catalog\Ui\Component\Product\Form\Categories\Options $options
     */
    public function __construct(\Magento\Catalog\Ui\Component\Product\Form\Categories\Options $options)
    {
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig(): array
    {
        return [
            'label' => __('Category'),
            'component' => 'Magento_Ui/js/form/element/ui-select',
            'template' => 'ui/grid/filters/elements/ui-select',
            'formElement' => 'select',
            'disableLabel' => true,
            'multiple' => false,
            'chipsEnabled' => false,
            'filterOptions' => true,
            'levelsVisibility' => '1',
            'options' => $this->options->toOptionArray(),
            'sortOrder' => 30,
            'missingValuePlaceholder' => __('Category with ID: %s doesn\'t exist'),
            'isDisplayMissingValuePlaceholder' => true,
            'isRemoveSelectedIcon' => true,
        ];
    }
}
