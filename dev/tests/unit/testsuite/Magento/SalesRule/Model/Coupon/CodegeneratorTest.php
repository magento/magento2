<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Coupon;

/**
 * Class CodegeneratorTest
 */
class CodegeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Model\Coupon\Codegenerator
     */
    protected $codegenerator;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->codegenerator = $objectManager->getObject('Magento\SalesRule\Model\Coupon\Codegenerator');
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
