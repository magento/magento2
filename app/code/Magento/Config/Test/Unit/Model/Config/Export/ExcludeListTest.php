<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Export;

use Magento\Config\Model\Config\Export\ExcludeList;
use PHPUnit\Framework\TestCase;

class ExcludeListTest extends TestCase
{
    /**
     * @var ExcludeList
     */
    private $model;

    protected function setUp(): void
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
