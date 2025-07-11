<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Edit\Button;

use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\CreateCategory;

class CreateCategoryTest extends GenericTest
{
    public function testGetButtonData()
    {
        $this->assertEquals(
            [
                'label' => __('Create Category'),
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save']],
                    'form-role' => 'save',
                ],
                'sort_order' => 10,
            ],
            $this->getModel(CreateCategory::class)->getButtonData()
        );
    }
}
