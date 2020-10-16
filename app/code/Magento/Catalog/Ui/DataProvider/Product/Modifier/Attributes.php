<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\DataProvider\Product\Modifier;

use Magento\Framework\Escaper;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

/**
 * Modify product listing attributes
 */
class Attributes implements ModifierInterface
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var array
     */
    private $escapeAttributes;

    /**
     * @param Escaper $escaper
     * @param array $escapeAttributes
     */
    public function __construct(
        Escaper $escaper,
        array $escapeAttributes = []
    ) {
        $this->escaper = $escaper;
        $this->escapeAttributes = $escapeAttributes;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        if (!empty($data) && !empty($this->escapeAttributes)) {
            foreach ($data['items'] as &$item) {
                foreach ($this->escapeAttributes as $escapeAttribute) {
                    if (isset($item[$escapeAttribute])) {
                        $item[$escapeAttribute] = $this->escaper->escapeHtml($item[$escapeAttribute]);
                    }
                }
            }
        }
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
