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
        $objectManagerMock = $this->getMock(
            'Magento\Framework\ObjectManager',
            ['get', 'create', 'configure'],
            [],
            '',
            false
        );
        $this->contentTypeFactory = new ContentTypeFactory($objectManagerMock);
        $objectManagerMock->expects($this->once())->method('get')->with($expected)->willReturn($contentRender);
        $this->assertInstanceOf($expected, $this->contentTypeFactory->get($type));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetTypeException()
    {
        $objectManagerMock = $this->getMock(
            'Magento\Framework\ObjectManager',
            ['get', 'create', 'configure'],
            [],
            '',
            false
        );
        $this->contentTypeFactory = new ContentTypeFactory($objectManagerMock);
        $this->contentTypeFactory->get('bad_type');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetInstanceException()
    {
        $objectManagerMock = $this->getMock(
            'Magento\Framework\ObjectManager',
            ['get', 'create', 'configure'],
            [],
            '',
            false
        );
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
