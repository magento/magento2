<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Test\Unit\Gateway\Helper;

use Magento\BraintreeTwo\Gateway\Helper\SubjectReader;
use InvalidArgumentException;

/**
 * Class SubjectReaderTest
 */
class SubjectReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    protected function setUp()
    {
        $this->subjectReader = new SubjectReader();
    }

    /**
     * @covers \Magento\BraintreeTwo\Gateway\Helper\SubjectReader::readCustomerId
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The "customerId" field does not exists
     */
    public function testReadCustomerIdWithException()
    {
        $this->subjectReader->readCustomerId([]);
    }

    /**
     * @covers \Magento\BraintreeTwo\Gateway\Helper\SubjectReader::readCustomerId
     */
    public function testReadCustomerId()
    {
        $customerId = 1;
        static::assertEquals($customerId, $this->subjectReader->readCustomerId(['customer_id' => $customerId]));
    }

    /**
     * @covers \Magento\BraintreeTwo\Gateway\Helper\SubjectReader::readPublicHash
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The "public_hash" field does not exists
     */
    public function testReadPublicHashWithException()
    {
        $this->subjectReader->readPublicHash([]);
    }

    /**
     * @covers \Magento\BraintreeTwo\Gateway\Helper\SubjectReader::readPublicHash
     */
    public function testReadPublicHash()
    {
        $hash = 'fj23djf2o1fd';
        static::assertEquals($hash, $this->subjectReader->readPublicHash(['public_hash' => $hash]));
    }
}
