<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Config\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ListPerPageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Config\Source\ListPerPage
     */
    private $model;

    protected function setUp()
    {
        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            \Magento\Catalog\Model\Config\Source\ListPerPage::class,
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
