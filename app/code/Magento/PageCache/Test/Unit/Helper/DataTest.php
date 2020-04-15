<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\PageCache\Helper\Data
 */
namespace Magento\PageCache\Test\Unit\Helper;

/**
 * Class DataTest
 *
 */
class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\View\Layout\ProcessorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $updateLayoutMock;

    /**
     * @var \Magento\Framework\App\Helper\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\App\View|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $viewMock;

    public function testMaxAgeCache()
    {
        // one year
        $age = 365 * 24 * 60 * 60;
        $this->assertEquals($age, \Magento\PageCache\Helper\Data::PRIVATE_MAX_AGE_CACHE);
    }
}
