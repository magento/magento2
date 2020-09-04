<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Config\Source;

use Magento\Catalog\Model\Config\Source\ListPerPage;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class ListPerPageTest extends TestCase
{
    /**
     * @var ListPerPage
     */
    private $model;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            ListPerPage::class,
            ['options' => 'some,test,options']
        );
    }

    public function testToOptionArray()
    {
        $expect = [
            ['value' => 'some', 'label' => 'some'],
            ['value' => 'test', 'label' => 'test'],
            ['value' => 'options', 'label' => 'options'],
        ];

        $this->assertEquals($expect, $this->model->toOptionArray());
    }
}
