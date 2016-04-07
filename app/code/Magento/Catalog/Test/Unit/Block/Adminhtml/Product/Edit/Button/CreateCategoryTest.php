<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Edit\Button;

use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\CreateCategory;

/**
 * Class CreateCategoryTest
 */
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
