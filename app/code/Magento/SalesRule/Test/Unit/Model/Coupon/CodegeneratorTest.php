<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Coupon;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SalesRule\Model\Coupon\Codegenerator;
use PHPUnit\Framework\TestCase;

class CodegeneratorTest extends TestCase
{
    /**
     * @var Codegenerator
     */
    protected $codegenerator;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->codegenerator = $objectManager->getObject(Codegenerator::class);
    }

    /**
     * Run test generateCode method
     */
    public function testGenerateCode()
    {
        $this->assertNotEmpty($this->codegenerator->generateCode());
    }

    /**
     * Run test getDelimiter method
     */
    public function testGetDelimiter()
    {
        $this->assertNotEmpty($this->codegenerator->getDelimiter());
    }
}
