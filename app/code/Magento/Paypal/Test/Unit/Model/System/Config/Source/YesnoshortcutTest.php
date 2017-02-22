<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\System\Config\Source;

use Magento\Paypal\Model\System\Config\Source\Yesnoshortcut;

class YesnoshortcutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Yesnoshortcut
     */
    protected $_model;

    public function setUp()
    {
        $this->_model = new Yesnoshortcut();
    }

    public function testToOptionArray()
    {
        $expectedResult = [
            ['value' => 1, 'label' => __('Yes (PayPal recommends this option)')],
            ['value' => 0, 'label' => __('No')]
        ];
        $this->assertEquals($expectedResult, $this->_model->toOptionArray());
    }
}
