<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
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
        $objectManager = new ObjectManager($this);
        $objectManager->prepareObjectManager();
        $this->model = $objectManager->getObject(Payflowadvanced::class);
    }

    /**
     * @covers \Magento\Paypal\Model\Payflowadvanced::getInfoBlockType()
     */
    public function testGetInfoBlockType()
    {
        static::assertEquals(Info::class, $this->model->getInfoBlockType());
    }
}
