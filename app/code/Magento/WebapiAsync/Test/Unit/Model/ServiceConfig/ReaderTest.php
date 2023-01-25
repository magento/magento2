<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Test\Unit\Model\ServiceConfig;

use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\WebapiAsync\Model\ServiceConfig\Converter;
use Magento\WebapiAsync\Model\ServiceConfig\Reader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var FileResolverInterface|MockObject
     */
    private $fileResolver;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->fileResolver = $this
            ->getMockForAbstractClass(FileResolverInterface::class);

        $this->reader = $objectManager->getObject(
            Reader::class,
            [
                'fileResolver' => $this->fileResolver,
                'converter' => $objectManager->getObject(Converter::class),
            ]
        );
    }

    /**
     * @covers \Magento\WebapiAsync\Model\ServiceConfig\Reader::read()
     */
    public function testReader()
    {
        $this->fileResolver->expects($this->once())
            ->method('get')
            ->with('webapi_async.xml', 'global')->willReturn([
                file_get_contents(__DIR__ . '/_files/Reader/webapi_async_1.xml'),
                file_get_contents(__DIR__ . '/_files/Reader/webapi_async_2.xml'),
            ]);

        $mergedConfiguration = include __DIR__ . '/_files/Reader/webapi_async.php';
        $readConfiguration = $this->reader->read();

        $this->assertEquals($mergedConfiguration, $readConfiguration);
    }
}
