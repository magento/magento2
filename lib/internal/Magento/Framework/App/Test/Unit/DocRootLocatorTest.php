<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\App\DocRootLocator;

class DocRootLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider isPubDataProvider
     *
     * @param string $path
     * @param bool $isExist
     * @param bool $result
     */
    public function testIsPub($path, $isExist, $result)
    {
        $request = $this->getMock('\Magento\Framework\App\Request\Http', [], [], '', false);
        $request->expects($this->once())->method('getServer')->willReturn($path);
        $reader = $this->getMock('\Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        $reader->expects($this->any())->method('isExist')->willReturn($isExist);
        $readFactory = $this->getMock('\Magento\Framework\Filesystem\Directory\ReadFactory', [], [], '', false);
        $readFactory->expects($this->once())->method('create')->willReturn($reader);
        $model = new DocRootLocator($request, $readFactory);
        $this->assertSame($result, $model->isPub());
    }

    /**
     * @return array
     */
    public function isPubDataProvider()
    {
        return [
            ['/some/path/to/root', false, false],
            ['/some/path/to/root', true, false],
            ['/some/path/to/pub', false, true],
            ['/some/path/to/pub', true, false],
        ];
    }
}
