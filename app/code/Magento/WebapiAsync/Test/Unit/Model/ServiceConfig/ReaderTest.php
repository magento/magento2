<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Test\Unit\Model\ServiceConfig;

use Magento\Framework\Config\FileResolverInterface;
use Magento\WebapiAsync\Model\ServiceConfig\Converter;
use Magento\WebapiAsync\Model\ServiceConfig\Reader;

class ReaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var FileResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $fileResolver;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->fileResolver = $this
            ->getMockForAbstractClass(\Magento\Framework\Config\FileResolverInterface::class);

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
