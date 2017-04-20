<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Paypal\Block\Payment\Info;
use Magento\Paypal\Model\Payflowadvanced;

class PayflowadvancedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Payflowadvanced
     */
    private $model;

    protected function setUp()
    {
        $this->model = (new ObjectManager($this))->getObject(Payflowadvanced::class);
    }

    /**
     * @covers \Magento\Paypal\Model\Payflowadvanced::getInfoBlockType()
     */
    public function testGetInfoBlockType()
    {
        static::assertEquals(Info::class, $this->model->getInfoBlockType());
    }
}
