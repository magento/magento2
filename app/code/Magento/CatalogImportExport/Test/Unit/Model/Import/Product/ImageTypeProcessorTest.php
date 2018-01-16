<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product;

use Magento\CatalogImportExport\Model\Import\Product\ImageTypeProcessor;

class ImageTypeProcessorTest extends \PHPUnit\Framework\TestCase
{
    public function testGetImageTypes()
    {
        $typeProcessor = new ImageTypeProcessor();
        $this->assertEquals(
            ['image', 'small_image', 'thumbnail', 'swatch_image', '_media_image'],
            $typeProcessor->getImageTypes()
        );
    }
}
