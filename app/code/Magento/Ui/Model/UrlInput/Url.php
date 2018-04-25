<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
