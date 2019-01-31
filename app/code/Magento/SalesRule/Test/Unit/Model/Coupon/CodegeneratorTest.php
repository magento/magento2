<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model\Coupon;

/**
 * Class CodegeneratorTest
 */
class CodegeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SalesRule\Model\Coupon\Codegenerator
     */
    protected $codegenerator;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->codegenerator = $objectManager->getObject(\Magento\SalesRule\Model\Coupon\Codegenerator::class);
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
