<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Category\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Catalog\Model\Category;

/**
 * Allows to select a layout file to merge when rendering a category's page.
 */
class LayoutUpdate extends AbstractBackend
{
    /**
     * @inheritDoc
     * @param Category $object
     */
    public function validate($object)
    {
        $valid = parent::validate($object);


        return $valid;
    }

    /**
     * @inheritDoc
     * @param Category $object
     */
    public function beforeSave($object)
    {
        parent::beforeSave($object);

        return $this;
    }
}
