<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Config\Source;

use Magento\Catalog\Model\Config\Source\GridPerPage;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class GridPerPageTest extends TestCase
{
    /**
     * @var GridPerPage
     */
    private $model;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            GridPerPage::class,
            ['perPageValues' => 'some,values']
        );
    }

    public function testToOptionalArray()
    {
        $expect = [
            ['value' => 'some', 'label' => 'some'],
            ['value' => 'values', 'label' => 'values'],
        ];

        $this->assertEquals($expect, $this->model->toOptionArray());
    }
}
