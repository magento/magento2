<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\PageCache\Helper\Data
 */
namespace Magento\PageCache\Test\Unit\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\View;
use Magento\Framework\View\Layout\ProcessorInterface;
use Magento\PageCache\Helper\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var ProcessorInterface|MockObject
     */
    protected $updateLayoutMock;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var View|MockObject
     */
    protected $viewMock;

    public function testMaxAgeCache()
    {
        // one year
        $age = 365 * 24 * 60 * 60;
        $this->assertEquals($age, Data::PRIVATE_MAX_AGE_CACHE);
    }
}
