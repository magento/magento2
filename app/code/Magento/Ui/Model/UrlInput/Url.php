<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Ui\Model\UrlInput;

/**
 * Returns configuration for default Url Input type
 */
class Url implements ConfigInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfig(): array
    {
        return [
            'label' => __('URL'),
            'component' => 'Magento_Ui/js/form/element/abstract',
            'template' => 'ui/form/element/input',
            'sortOrder' => 20,
        ];
    }
}
