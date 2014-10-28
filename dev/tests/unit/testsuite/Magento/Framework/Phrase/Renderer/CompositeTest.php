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
namespace Magento\Framework\Phrase\Renderer;

class CompositeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Composite
     */
    protected $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $rendererOne;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $rendererTwo;

    protected function setUp()
    {
        $this->rendererOne = $this->getMock('Magento\Framework\Phrase\RendererInterface');
        $this->rendererTwo = $this->getMock('Magento\Framework\Phrase\RendererInterface');
        $this->object = new \Magento\Framework\Phrase\Renderer\Composite(array($this->rendererOne, $this->rendererTwo));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Instance of the phrase renderer is expected, got stdClass instead
     */
    public function testConstructorException()
    {
        new \Magento\Framework\Phrase\Renderer\Composite(array(new \stdClass()));
    }

    public function testRender()
    {
        $text = 'some text';
        $arguments = ['arg1', 'arg2'];
        $resultAfterFirst = 'rendered text first';
        $resultAfterSecond = 'rendered text second';

        $this->rendererOne->expects(
            $this->once()
        )->method(
                'render'
            )->with(
                [$text],
                $arguments
            )->will(
                $this->returnValue($resultAfterFirst)
            );

        $this->rendererTwo->expects(
            $this->once()
        )->method(
                'render'
            )->with(
                [
                    $text, 
                    $resultAfterFirst
                ],
                $arguments
            )->will(
                $this->returnValue($resultAfterSecond)
            );

        $this->assertEquals($resultAfterSecond, $this->object->render([$text], $arguments));
    }
}
