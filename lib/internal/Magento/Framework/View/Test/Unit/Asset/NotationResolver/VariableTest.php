<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Asset\NotationResolver;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\File\FallbackContext;
use Magento\Framework\View\Asset\NotationResolver\Variable;
use Magento\Framework\View\Asset\Repository;

class VariableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FallbackContext|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $assetRepo;

    /**
     * @var Variable
     */
    private $object;

    protected function setUp()
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
            ->will($this->returnValue($this->context));

        $this->object = new Variable($this->assetRepo);
    }

    /**
     * @param $path
     * @param $expectedResult
     * @dataProvider convertVariableNotationDataProvider
     */
    public function testConvertVariableNotation($path, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->object->convertVariableNotation($path));
    }

    /**
     * @return array
     */
    public function convertVariableNotationDataProvider()
    {
        return [
            ['{{base_url_path}}/file.ext', '{{base_url_path}}frontend/Magento/blank/{{locale}}/file.ext'],
        ];
    }
}
