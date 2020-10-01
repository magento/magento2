<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Tests for \Magento\Framework\Data\Form\Element\Link
 */
namespace Magento\Framework\Data\Test\Unit\Form\Element;

use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Link;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class LinkTest extends TestCase
{
    private const RANDOM_STRING = '123456abcdef';

    /**
     * @var MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var Link
     */
    protected $_link;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $factoryMock = $this->createMock(Factory::class);
        $collectionFactoryMock = $this->createMock(CollectionFactory::class);
        $escaperMock = $objectManager->getObject(Escaper::class);
        $randomMock = $this->createMock(Random::class);
        $randomMock->method('getRandomString')->willReturn(self::RANDOM_STRING);
        $this->_link = new Link(
            $factoryMock,
            $collectionFactoryMock,
            $escaperMock,
            [],
            $this->createMock(SecureHtmlRenderer::class),
            $randomMock
        );
        $formMock = new DataObject();
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
            "link_before<a id=\"link_id\" formelementhookid=\"elemId" .self::RANDOM_STRING
            ."\" data-ui-id=\"form-element-\">Link Text</a>\nlink_after",
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
