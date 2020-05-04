<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Response\HeaderProvider;

use Magento\Framework\App\Response\HeaderProvider\XFrameOptions;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\TestCase;

class XFrameOptionsTest extends TestCase
{
    /** X-Frame-Option Header name */
    const HEADER_NAME = 'X-Frame-Options';

    /**
     * X-Frame-Option header value
     */
    const HEADER_VALUE = 'TEST_OPTION';

    /**
     * @var XFrameOptions
     */
    protected $object;

    protected function setUp(): void
    {
        $objectManager = new ObjectManagerHelper($this);
        $this->object = $objectManager->getObject(
            XFrameOptions::class,
            ['xFrameOpt' => $this::HEADER_VALUE]
        );
    }

    public function testGetName()
    {
        $this->assertEquals($this::HEADER_NAME, $this->object->getName(), 'Wrong header name');
    }

    public function testGetValue()
    {
        $this->assertEquals($this::HEADER_VALUE, $this->object->getValue(), 'Wrong header value');
    }

    /**
     * @param bool $expected
     */
    public function testCanApply()
    {
        $this->assertTrue($this->object->canApply(), 'Incorrect canApply result');
    }
}
