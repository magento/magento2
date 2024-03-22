<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav;

/**
 * Interface WysiwygConfigDataProcessorInterface
 *
 * @api
 */
interface WysiwygConfigDataProcessorInterface
{
    /**
     * Returns wysiwygConfigData array to render wysiwyg ui component
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute
     * @return array
     */
    public function process(\Magento\Catalog\Api\Data\ProductAttributeInterface $attribute);
}
