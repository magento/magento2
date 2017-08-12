<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\System\Config\Source;

use Magento\Paypal\Model\System\Config\Source\Yesnoshortcut;

class YesnoshortcutTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Yesnoshortcut
     */
    protected $_model;

    protected function setUp()
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
