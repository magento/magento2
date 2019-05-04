<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\Config\Rules;

/**
 * Class FileResolverTest
 *
 * Test for class \Magento\Paypal\Model\Config\Rules\FileResolver
 */
class FileResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @vat \Magento\Paypal\Model\Config\Rules\FileResolver
     */
    protected $fileResolver;

    /**
     * @var \Magento\Framework\Module\Dir\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->readerMock = $this->getMockBuilder(\Magento\Framework\Module\Dir\Reader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileResolver = new \Magento\Paypal\Model\Config\Rules\FileResolver($this->readerMock);
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
