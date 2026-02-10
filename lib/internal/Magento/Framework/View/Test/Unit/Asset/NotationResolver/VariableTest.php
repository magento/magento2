<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Asset\NotationResolver;

use Magento\Framework\View\Asset\File\FallbackContext;
use Magento\Framework\View\Asset\NotationResolver\Variable;
use Magento\Framework\View\Asset\Repository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class VariableTest extends TestCase
{
    /**
     * @var FallbackContext|MockObject
     */
    private $context;

    /**
     * @var Repository|MockObject
     */
    private $assetRepo;

    /**
     * @var Variable
     */
    private $object;

    protected function setUp(): void
    {
        $area = 'frontend';
        $themePath = 'Magento/blank';

        $this->context = $this->getMockBuilder(FallbackContext::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->once())
            ->method('getAreaCode')
            ->willReturn($area);
        $this->context->expects($this->exactly(2))
            ->method('getThemePath')
            ->willReturn($themePath);

        $this->assetRepo = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->assetRepo->expects($this->any())
            ->method('getStaticViewFileContext')
            ->willReturn($this->context);

        $this->object = new Variable($this->assetRepo);
    }

    /**
     * @param $path
     * @param $expectedResult     */
    #[DataProvider('convertVariableNotationDataProvider')]
    public function testConvertVariableNotation($path, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->object->convertVariableNotation($path));
    }

    /**
     * @return array
     */
    public static function convertVariableNotationDataProvider()
    {
        return [
            ['{{base_url_path}}/file.ext', '{{base_url_path}}frontend/Magento/blank/{{locale}}/file.ext'],
        ];
    }
}
