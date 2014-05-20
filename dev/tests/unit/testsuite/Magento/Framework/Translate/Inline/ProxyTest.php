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
namespace Magento\Framework\Translate\Inline;

class ProxyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Framework\Translate\Inline|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $translateMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMock(
            'Magento\Framework\ObjectManager',
            array('get', 'create', 'configure'),
            array(),
            '',
            false
        );
        $this->translateMock = $this->getMock('Magento\Framework\Translate\Inline', array(), array(), '', false);
    }

    public function testIsAllowed()
    {
        $this->objectManagerMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'Magento\Framework\Translate\Inline'
        )->will(
            $this->returnValue($this->translateMock)
        );
        $this->objectManagerMock->expects($this->never())->method('create');
        $this->translateMock->expects($this->once())->method('isAllowed')->will($this->returnValue(false));

        $model = new Proxy(
            $this->objectManagerMock,
            'Magento\Framework\Translate\Inline',
            true
        );

        $this->assertFalse($model->isAllowed());
    }

    public function testGetParser()
    {
        $parser = new \stdClass();
        $this->objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Magento\Framework\Translate\Inline'
        )->will(
            $this->returnValue($this->translateMock)
        );
        $this->objectManagerMock->expects($this->never())->method('get');
        $this->translateMock->expects($this->once())->method('getParser')->will($this->returnValue($parser));


        $model = new Proxy(
            $this->objectManagerMock,
            'Magento\Framework\Translate\Inline',
            false
        );

        $this->assertEquals($parser, $model->getParser());
    }

    public function testProcessResponseBody()
    {
        $isJson = true;
        $this->objectManagerMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'Magento\Framework\Translate\Inline'
        )->will(
            $this->returnValue($this->translateMock)
        );
        $this->objectManagerMock->expects($this->never())->method('create');

        $this->translateMock->expects($this->once())
            ->method('processResponseBody')
            ->with('', $isJson)
            ->will($this->returnSelf());

        $model = new Proxy(
            $this->objectManagerMock,
            'Magento\Framework\Translate\Inline',
            true
        );
        $body = '';

        $this->assertEquals($this->translateMock, $model->processResponseBody($body, $isJson));
    }

    public function testGetAdditionalHtmlAttribute()
    {
        $this->objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Magento\Framework\Translate\Inline'
        )->will(
            $this->returnValue($this->translateMock)
        );
        $this->objectManagerMock->expects($this->never())->method('get');
        $this->translateMock->expects($this->exactly(2))
            ->method('getAdditionalHtmlAttribute')
            ->with($this->logicalOr('some_value', null))
            ->will($this->returnArgument(0));

        $model = new Proxy(
            $this->objectManagerMock,
            'Magento\Framework\Translate\Inline',
            false
        );

        $this->assertEquals('some_value', $model->getAdditionalHtmlAttribute('some_value'));
        $this->assertNull($model->getAdditionalHtmlAttribute());
    }
}
