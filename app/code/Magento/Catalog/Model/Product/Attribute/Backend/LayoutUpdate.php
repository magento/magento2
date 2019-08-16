<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Catalog\Model\Product;

/**
 * Allows to select a layout file to merge when rendering the product's page.
 */
class LayoutUpdate extends AbstractBackend
{
    /**
     * @inheritDoc
     * @param Product $object
     */
    public function validate($object)
    {
        $valid = parent::validate($object);


        return $valid;
    }

    /**
     * @inheritDoc
     * @param Product $object
     */
    public function beforeSave($object)
    {
        parent::beforeSave($object);

        return $this;
    }
}
