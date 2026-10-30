<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Block\Adminhtml\Form\Element\Address;

use Magento\Customer\Block\Adminhtml\Form\Element\Address\Image;

/**
 * Test customer address image element block
 */
class ImageTest extends FileTest
{
    /**
     * @inheritdoc
     */
    public function modelClass(): string
    {
        return Image::class;
    }
}
