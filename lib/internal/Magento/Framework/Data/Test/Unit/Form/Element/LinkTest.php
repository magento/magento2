<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests for \Magento\Framework\Data\Form\Element\Link
 */
namespace Magento\Framework\Data\Test\Unit\Form\Element;

class LinkTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \Magento\Framework\Data\Form\Element\Link
     */
    protected $_link;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $factoryMock = $this->createMock(\Magento\Framework\Data\Form\Element\Factory::class);
        $collectionFactoryMock = $this->createMock(\Magento\Framework\Data\Form\Element\CollectionFactory::class);
        $escaperMock = $objectManager->getObject(\Magento\Framework\Escaper::class);
        $this->_link = new \Magento\Framework\Data\Form\Element\Link(
            $factoryMock,
            $collectionFactoryMock,
            $escaperMock
        );
        $formMock = new \Magento\Framework\DataObject();
        $formMock->getHtmlIdPrefix('id_prefix');
        $formMock->getHtmlIdPrefix('id_suffix');
        $this->_link->setForm($formMock);
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\Link::__construct
     */
    public function testConstruct()
    {
        $this->assertEquals('link', $this->_link->getType());
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\Link::getElementHtml
     */
    public function testGetElementHtml()
    {
        $this->_link->setBeforeElementHtml('link_before');
        $this->_link->setAfterElementHtml('link_after');
        $this->_link->setId('link_id');
        $this->_link->setData('ui_id', 'ui_id');
        $this->_link->setValue('Link Text');
        $html = $this->_link->getElementHtml();
        $this->assertEquals(
            "link_before<a id=\"link_id\"  data-ui-id=\"form-element-\">Link Text</a>\nlink_after",
            $html
        );
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\Link::getHtmlAttributes
     */
    public function testGetHtmlAttributes()
    {
        $this->assertEmpty(
            array_diff(
                [
                    'charset',
                    'coords',
                    'href',
                    'hreflang',
                    'rel',
                    'rev',
                    'name',
                    'shape',
                    'target',
                    'accesskey',
                    'class',
                    'dir',
                    'lang',
                    'style',
                    'tabindex',
                    'title',
                    'xml:lang',
                    'onblur',
                    'onclick',
                    'ondblclick',
                    'onfocus',
                    'onmousedown',
                    'onmousemove',
                    'onmouseout',
                    'onmouseover',
                    'onmouseup',
                    'onkeydown',
                    'onkeypress',
                    'onkeyup',
                ],
                $this->_link->getHtmlAttributes()
            )
        );
    }
}
