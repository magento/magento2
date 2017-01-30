<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Asset\NotationResolver;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Asset\NotationResolver;

class VariableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Asset\File\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var \Magento\Framework\View\Asset\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $assetRepo;

    /**
     * @var \Magento\Framework\View\Asset\NotationResolver\Variable
     */
    private $object;

    protected function setUp()
    {
        $baseUrl = 'http://example.com/pub/static/';
        $path = 'frontend/Magento/blank/en_US';

        $this->context = $this->getMock(
            '\Magento\Framework\View\Asset\File\Context',
            null,
            [$baseUrl, DirectoryList::STATIC_VIEW, $path]
        );

        $this->assetRepo = $this->getMock('Magento\Framework\View\Asset\Repository', [], [], '', false);
        $this->assetRepo->expects($this->any())
            ->method('getStaticViewFileContext')
            ->will($this->returnValue($this->context));

        $this->object = new \Magento\Framework\View\Asset\NotationResolver\Variable($this->assetRepo);
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
            ['{{base_url_path}}/file.ext', 'http://example.com/pub/static/frontend/Magento/blank/en_US/file.ext'],
        ];
    }
}
