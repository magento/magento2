<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\View\Asset\PreProcessor;

class ChainTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Asset\LocalInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $asset;

    /**
     * @var Chain
     */
    private $object;

    protected function setUp()
    {
        $this->asset = $this->getMockForAbstractClass('\Magento\Framework\View\Asset\LocalInterface');
        $this->asset->expects($this->once())->method('getContentType')->will($this->returnValue('assetType'));
        $this->object = new Chain($this->asset, 'origContent', 'origType');
    }

    public function testGetAsset()
    {
        $this->assertSame($this->asset, $this->object->getAsset());
    }

    public function testGettersAndSetters()
    {
        $this->assertEquals('origType', $this->object->getOrigContentType());
        $this->assertEquals('origType', $this->object->getContentType());
        $this->assertEquals('origContent', $this->object->getOrigContent());
        $this->assertEquals('origContent', $this->object->getContent());
        $this->assertEquals('assetType', $this->object->getTargetContentType());

        $this->object->setContent('anotherContent');
        $this->object->setContentType('anotherType');

        $this->assertEquals('origType', $this->object->getOrigContentType());
        $this->assertEquals('anotherType', $this->object->getContentType());
        $this->assertEquals('origContent', $this->object->getOrigContent());
        $this->assertEquals('anotherContent', $this->object->getContent());
        $this->assertEquals('assetType', $this->object->getTargetContentType());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage The requested asset type was 'assetType', but ended up with 'type'
     */
    public function testAssertValid()
    {
        $this->object->setContentType('type');
        $this->object->assertValid();
    }

    /**
     * @param string $content
     * @param string $type
     * @param bool $expected
     * @dataProvider isChangedDataProvider
     */
    public function testIsChanged($content, $type, $expected)
    {
        $this->object->setContent($content);
        $this->object->setContentType($type);
        $this->assertEquals($expected, $this->object->isChanged());
    }

    /**
     * @return array
     */
    public function isChangedDataProvider()
    {
        return [
            ['origContent', 'origType', false],
            ['anotherContent', 'origType', true],
            ['origContent', 'anotherType', true],
            ['anotherContent', 'anotherType', true],
        ];
    }
} 
