<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Framework\Stdlib\ArrayManager;

class Seo extends AbstractModifier
{
    /**
     * @var ArrayManager
     */
    private ArrayManager $arrayManager;

    /**
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
    }

    /**
     * @inheritDoc
     */
    public function modifyData(array $data): array
    {
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function modifyMeta(array $meta): array
    {
        $meta = $this->customizeUrlKeyField($meta);

        return $meta;
    }

    /**
     * Customize URL KEY field
     *
     * @param  array $meta
     * @return array
     */
    protected function customizeUrlKeyField(array $meta): array
    {
        $urlKeyConfig = [
            'tooltip' => [
                'link' => 'https://docs.magento.com/user-guide/catalog/catalog-urls.html',
                'description' => __(
                    'The URL key should consist of lowercase characters with hyphens to separate words.'
                ),
            ],
        ];

        $path = $this->arrayManager->findPath(
            ProductAttributeInterface::CODE_SEO_FIELD_URL_KEY,
            $meta,
            null,
            'children'
        );
        return $this->arrayManager->merge($path . static::META_CONFIG_PATH, $meta, $urlKeyConfig);
    }
}
