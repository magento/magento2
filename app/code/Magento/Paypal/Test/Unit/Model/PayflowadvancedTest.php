<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Paypal\Block\Payment\Info;
use Magento\Paypal\Model\Payflowadvanced;
use PHPUnit\Framework\TestCase;

class PayflowadvancedTest extends TestCase
{
    /**
     * @var Payflowadvanced
     */
    private $model;

    protected function setUp(): void
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
