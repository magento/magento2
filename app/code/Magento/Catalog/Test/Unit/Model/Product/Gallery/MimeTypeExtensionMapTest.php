<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Test\Unit\Model\Product\Gallery;

class MimeTypeExtensionMapTest extends \PHPUnit_Framework_TestCase
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
        $this->assertEquals("jpg", $this->model->getMimeTypeExtension("image/jpeg"));
        $this->assertEquals("jpg", $this->model->getMimeTypeExtension("image/jpg"));
        $this->assertEquals("png", $this->model->getMimeTypeExtension("image/png"));
        $this->assertEquals("gif", $this->model->getMimeTypeExtension("image/gif"));
        $this->assertEquals("", $this->model->getMimeTypeExtension("unknown"));
        $this->assertEquals("", $this->model->getMimeTypeExtension(null));
    }
}
