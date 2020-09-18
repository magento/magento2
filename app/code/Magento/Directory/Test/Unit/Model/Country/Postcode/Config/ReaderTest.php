<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Model\Country\Postcode\Config;

use Magento\Directory\Model\Country\Postcode\Config\Converter;
use Magento\Directory\Model\Country\Postcode\Config\Reader as PostcodeReader;
use Magento\Directory\Model\Country\Postcode\Config\SchemaLocator;
use Magento\Framework\App\Config\FileResolver;
use Magento\Framework\Config\ValidationStateInterface;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    /**
     * @var \Magento\Sales\Model\Config\Reader
     */
    protected $reader;

    /**
     * Prepare parameters
     */
    protected function setUp(): void
    {
        $fileResolver = $this->getMockBuilder(
            FileResolver::class
        )->disableOriginalConstructor()
            ->getMock();
        $converter = $this->getMockBuilder(
            Converter::class
        )->disableOriginalConstructor()
            ->getMock();
        $schema = $this->getMockBuilder(
            SchemaLocator::class
        )->disableOriginalConstructor()
            ->getMock();
        $validator = $this->getMockBuilder(
            ValidationStateInterface::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->reader = new PostcodeReader(
            $fileResolver,
            $converter,
            $schema,
            $validator
        );
    }

    /**
     * Test creating object
     */
    public function testInstanceof()
    {
        $this->assertInstanceOf(PostcodeReader::class, $this->reader);
    }
}
