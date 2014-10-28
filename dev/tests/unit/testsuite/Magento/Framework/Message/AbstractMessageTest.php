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
namespace Magento\Framework\Message;

/**
 * \Magento\Framework\Message\AbstractMessage test case
 */
class AbstractMessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Message\AbstractMessage
     */
    protected $model;

    public function setUp()
    {
        $this->model = $this->getMockBuilder(
            'Magento\Framework\Message\AbstractMessage'
        )->disableOriginalConstructor()->setMethods(
            array('getType')
        )->getMockForAbstractClass();
    }

    /**
     * @covers \Magento\Framework\Message\AbstractMessage::getText
     * @covers \Magento\Framework\Message\AbstractMessage::setText
     * @dataProvider setTextGetTextProvider
     */
    public function testSetTextGetText($text)
    {
        $this->model->setText($text);
        $this->assertEquals($text, $this->model->getText());
    }

    /**
     * @return array
     */
    public function setTextGetTextProvider()
    {
        return array(array(''), array('some text'));
    }

    /**
     * @covers \Magento\Framework\Message\AbstractMessage::getIdentifier
     * @covers \Magento\Framework\Message\AbstractMessage::setIdentifier
     * @dataProvider setIdentifierGetIdentifierProvider
     */
    public function testSetIdentifierGetIdentifier($identifier)
    {
        $this->model->setIdentifier($identifier);
        $this->assertEquals($identifier, $this->model->getIdentifier());
    }

    /**
     * @return array
     */
    public function setIdentifierGetIdentifierProvider()
    {
        return array(array(''), array('some identifier'));
    }

    /**
     * @covers \Magento\Framework\Message\AbstractMessage::getIsSticky
     * @covers \Magento\Framework\Message\AbstractMessage::setIsSticky
     */
    public function testSetIsStickyGetIsSticky()
    {
        $this->assertFalse($this->model->getIsSticky());
        $this->model->setIsSticky();
        $this->assertTrue($this->model->getIsSticky());
    }

    /**
     * @covers \Magento\Framework\Message\AbstractMessage::toString
     */
    public function testToString()
    {
        $someText = 'some text';
        $expectedString = MessageInterface::TYPE_SUCCESS . ': ' . $someText;

        $this->model->expects(
            $this->atLeastOnce()
        )->method(
            'getType'
        )->will(
            $this->returnValue(MessageInterface::TYPE_SUCCESS)
        );

        $this->model->setText($someText);
        $this->assertEquals($expectedString, $this->model->toString());
    }
}
