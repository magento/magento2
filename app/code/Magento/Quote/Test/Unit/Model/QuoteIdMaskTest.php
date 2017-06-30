<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Test\Unit\Model;

use Magento\Framework\Math\Random;

/**
 * Unit test for \Magento\Quote\Model\QuoteIdMask
 */
class QuoteIdMaskTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Model\QuoteIdMask
     */
    protected $quoteIdMask;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->quoteIdMask = $helper->getObject(
            \Magento\Quote\Model\QuoteIdMask::class,
            ['randomDataGenerator' => new Random()]
        );
    }

    public function testBeforeSave()
    {
        $this->quoteIdMask->beforeSave();
        $this->assertNotNull($this->quoteIdMask->getMaskedId(), 'Masked identifier is not generated.');
    }
}
