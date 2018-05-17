<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Test\Unit\Model\RouteCustomizationConfig;

use Magento\Framework\Config\FileResolverInterface;
use Magento\WebapiAsync\Model\RouteCustomizationConfig\Converter;
use Magento\WebapiAsync\Model\RouteCustomizationConfig\Reader;

class ReaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var FileResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileResolver;

    public function setUp()
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
     * @covers \Magento\WebapiAsync\Model\RouteCustomizationConfig\Reader::read()
     */
    public function testReader()
    {
        $this->fileResolver->expects($this->once())
            ->method('get')
            ->with('route_customization.xml', 'global')->willReturn([
                file_get_contents(__DIR__ . '/_files/Reader/route_customization1.xml'),
                file_get_contents(__DIR__ . '/_files/Reader/route_customization2.xml'),
            ]);

        $mergedConfiguration = include __DIR__ . '/_files/Reader/route_customization.php';
        $readConfiguration = $this->reader->read();

        $this->assertEquals($mergedConfiguration, $readConfiguration);
    }
}
