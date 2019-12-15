<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Eav\Model\Attribute\DataProvider;

/**
 * Product attribute data for attribute with input type dropdown.
 */
class DropDown extends AbstractAttributeDataWithOptions
{
    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->defaultAttributePostData['used_for_sort_by'] = '0';
        $this->defaultAttributePostData['swatch_input_type'] = 'dropdown';
    }

    /**
     * @inheritdoc
     */
    protected function getFrontendInput(): string
    {
        return 'select';
    }
}
