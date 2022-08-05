<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model\Config\Rules;

use Magento\Framework\Module\Dir\Reader;
use Magento\Paypal\Model\Config\Rules\FileResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class FileResolverTest
 *
 * Test for class \Magento\Paypal\Model\Config\Rules\FileResolver
 */
class FileResolverTest extends TestCase
{
    /**
     * @vat \Magento\Paypal\Model\Config\Rules\FileResolver
     */
    protected $fileResolver;

    /**
     * @var Reader|MockObject
     */
    protected $readerMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->readerMock = $this->getMockBuilder(Reader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileResolver = new FileResolver($this->readerMock);
    }

    /**
     * Run test for get method
     *
     * @return void
     */
    public function testGet()
    {
        $filename = 'test-filename';
        $expected = ['file1', 'file2'];

        $this->readerMock->expects($this->once())
            ->method('getConfigurationFiles')
            ->with($filename)
            ->willReturn($expected);

        $this->assertEquals($expected, $this->fileResolver->get($filename, null));
    }
}
