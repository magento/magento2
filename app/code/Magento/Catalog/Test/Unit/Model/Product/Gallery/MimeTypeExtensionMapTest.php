<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Gallery;

class MimeTypeExtensionMapTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Gallery\MimeTypeExtensionMap
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new \Magento\Catalog\Model\Product\Gallery\MimeTypeExtensionMap();
    }

    public function testGetMimeTypeExtension()
    {
        $this->assertSame("jpg", $this->model->getMimeTypeExtension("image/jpeg"));
        $this->assertSame("jpg", $this->model->getMimeTypeExtension("image/jpg"));
        $this->assertSame("png", $this->model->getMimeTypeExtension("image/png"));
        $this->assertSame("gif", $this->model->getMimeTypeExtension("image/gif"));
        $this->assertSame("", $this->model->getMimeTypeExtension("unknown"));
        $this->assertSame("", $this->model->getMimeTypeExtension(null));
    }
}
