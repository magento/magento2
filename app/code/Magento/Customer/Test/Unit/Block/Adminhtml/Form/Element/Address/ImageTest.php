<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
