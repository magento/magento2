<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Component;

/**
 * UrlFilterApplier component
 */
class UrlFilterApplier extends AbstractComponent
{
    const NAME = 'urlFilterApplier';

    /**
     * @inheritdoc
     */
    public function prepare(): void
    {
        parent::prepare();
        $filters = is_array($this->getContext()->getRequestParam('filters')) ?
            $this->getContext()->getRequestParam('filters') : null;

        $this->setData(
            'config',
            array_replace_recursive(
                (array)$this->getData('config'),
                [
                    'filters' => $filters,
                ]
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function getComponentName()
    {
        return static::NAME;
    }
}
