<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\_files;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Creates mock source for multiselect attributes
 */
class MultiselectSourceMock extends AbstractSource
{

    public function getAllOptions()
    {
        return [
            ['value' => 1, 'label' => 'Option 1'],
            ['value' => 2, 'label' => 'Option 2'],
            ['value' => 3, 'label' => 'Option 3'],
            ['value' => 4, 'label' => 'Option 4 "!@#$%^&*'],
        ];
    }
}
