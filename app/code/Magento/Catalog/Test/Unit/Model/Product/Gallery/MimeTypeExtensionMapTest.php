<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Gallery;

use Magento\Catalog\Model\Product\Gallery\MimeTypeExtensionMap;
use PHPUnit\Framework\TestCase;

class MimeTypeExtensionMapTest extends TestCase
{
    /**
     * @var MimeTypeExtensionMap
     */
    protected $model;

    protected function setUp(): void
    {
        $this->model = new MimeTypeExtensionMap();
    }

    public function testGetMimeTypeExtension()
    {
        $this->assertEquals("jpg", $this->model->getMimeTypeExtension("image/jpeg"));
        $this->assertEquals("jpg", $this->model->getMimeTypeExtension("image/jpg"));
        $this->assertEquals("png", $this->model->getMimeTypeExtension("image/png"));
        $this->assertEquals("gif", $this->model->getMimeTypeExtension("image/gif"));
        $this->assertEquals("", $this->model->getMimeTypeExtension("unknown"));
        $this->assertEquals("", $this->model->getMimeTypeExtension(null));
    }
}
