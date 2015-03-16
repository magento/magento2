<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Model\View\Asset\PreProcessor;

use Magento\Framework\View\Asset\LocalInterface;

class DeveloperChainTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LocalInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $asset;

    /**
     * @var DeveloperChain
     */
    private $object;

    protected function setUp()
    {
        $this->asset = $this->getMockForAbstractClass('\Magento\Framework\View\Asset\LocalInterface');
    }

    public function testChainTargetAssetPathDevMode()
    {
        $assetPath = 'assetPath';
        $origPath = 'origPath';

        $this->asset = $this->getMockForAbstractClass('\Magento\Framework\View\Asset\LocalInterface');
        $this->asset->expects($this->once())
            ->method('getContentType')
            ->will($this->returnValue('assetType'));
        $this->asset->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue($assetPath));
        $this->object = new DeveloperChain(
            $this->asset,
            'origContent',
            'origType',
            $origPath
        );

        $this->assertSame($this->object->getTargetAssetPath(), $origPath);
        $this->assertNotSame($this->object->getTargetAssetPath(), $assetPath);
    }
}
