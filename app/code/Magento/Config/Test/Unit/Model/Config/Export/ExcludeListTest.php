<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Export;

use Magento\Config\Model\Config\Export\ExcludeList;

class ExcludeListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ExcludeList
     */
    private $model;

    protected function setUp()
    {
        $this->model = new ExcludeList(
            [
                'web/unsecure/base_url' => '',
                'web/test/test_value' => '0',
                'web/test/test_sensitive' => '1',
            ]
        );
    }

    public function testGet()
    {
        $this->assertEquals(['web/test/test_sensitive'], $this->model->get());
    }

    public function testIsPresent()
    {
        $this->assertFalse($this->model->isPresent('some/new/path'));
        $this->assertFalse($this->model->isPresent('web/unsecure/base_url'));
        $this->assertFalse($this->model->isPresent('web/test/test_value'));
        $this->assertTrue($this->model->isPresent('web/test/test_sensitive'));
    }
}
