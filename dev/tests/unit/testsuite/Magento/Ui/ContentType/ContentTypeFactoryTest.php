<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\ContentType;

/**
 * Class ContentTypeFactoryTest
 */
class ContentTypeFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContentTypeFactory
     */
    protected $contentTypeFactory;

    /**
     * @param $type
     * @param @expected
     * @dataProvider getDataProvider
     */
    public function testGet($type, $contentRender, $expected)
    {
        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->contentTypeFactory = new ContentTypeFactory($objectManagerMock);
        $objectManagerMock->expects($this->once())->method('get')->with($expected)->willReturn($contentRender);
        $this->assertInstanceOf($expected, $this->contentTypeFactory->get($type));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetTypeException()
    {
        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->contentTypeFactory = new ContentTypeFactory($objectManagerMock);
        $this->contentTypeFactory->get('bad_type');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetInstanceException()
    {
        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->contentTypeFactory = new ContentTypeFactory($objectManagerMock);
        $objectManagerMock->expects($this->once())->method('get')->willReturnSelf();
        $this->contentTypeFactory->get();
    }

    /**
     * @return array
     */
    public function getDataProvider()
    {
        $htmlMock = $this->getMock('Magento\Ui\ContentType\Html', [], [], '', false);
        $jsonMock = $this->getMock('Magento\Ui\ContentType\Json', [], [], '', false);
        $xmlMock = $this->getMock('Magento\Ui\ContentType\Xml', [], [], '', false);
        return [
            ['html', $htmlMock, 'Magento\Ui\ContentType\Html'],
            ['json', $jsonMock, 'Magento\Ui\ContentType\Json'],
            ['xml', $xmlMock, 'Magento\Ui\ContentType\Xml']
        ];
    }
}
