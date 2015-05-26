<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests for \Magento\Framework\Data\Form\Element\Textarea
 */
namespace Magento\Framework\Data\Test\Unit\Form\Element;

use Magento\Framework\Data\Form\Element\Editor;

class EditorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Editor
     */
    protected $model;

    /**
     * @var \Magento\Framework\Data\Form\Element\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $factoryMock;

    /**
     * @var \Magento\Framework\Data\Form\Element\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $escaperMock;

    /**
     * @var \Magento\Framework\Object
     */
    protected $formMock;

    protected function setUp()
    {
        $this->factoryMock = $this->getMock('\Magento\Framework\Data\Form\Element\Factory', [], [], '', false);
        $this->collectionFactoryMock = $this->getMock(
            '\Magento\Framework\Data\Form\Element\CollectionFactory',
            [],
            [],
            '',
            false
        );
        $this->escaperMock = $this->getMock('\Magento\Framework\Escaper', [], [], '', false);

        $this->model = new Editor(
            $this->factoryMock,
            $this->collectionFactoryMock,
            $this->escaperMock
        );

        $this->formMock = new \Magento\Framework\Object();
        $this->formMock->getHtmlIdPrefix('id_prefix');
        $this->formMock->getHtmlIdPrefix('id_suffix');

        $this->model->setForm($this->formMock);
    }

    public function testConstruct()
    {
        $this->assertEquals('textarea', $this->model->getType());
        $this->assertEquals('textarea', $this->model->getExtType());
        $this->assertEquals(Editor::DEFAULT_ROWS, $this->model->getRows());
        $this->assertEquals(Editor::DEFAULT_COLS, $this->model->getCols());

        $config = new \Magento\Framework\Object();
        $config->setData('enabled', true);
        $model = new Editor(
            $this->factoryMock,
            $this->collectionFactoryMock,
            $this->escaperMock,
            ['config' => $config]
        );
        $model->setForm($this->formMock);

        $this->assertEquals('wysiwyg', $model->getType());
        $this->assertEquals('wysiwyg', $model->getExtType());
    }

    public function testGetElementHtml()
    {
        $html = $this->model->getElementHtml();
        $this->assertContains('</textarea>', $html);
        $this->assertContains('rows="2"', $html);
        $this->assertContains('cols="15"', $html);
        $this->assertRegExp('/class=\".*textarea.*\"/i', $html);
        $this->assertNotRegExp('/.*mage\/adminhtml\/wysiwyg\/widget.*/i', $html);

        $this->model->getConfig()->setData('enabled', true);
        $html = $this->model->getElementHtml();
        $this->assertRegExp('/.*mage\/adminhtml\/wysiwyg\/widget.*/i', $html);

        $this->model->getConfig()->setData('widget_window_url', 'localhost');
        $this->model->getConfig()->unsetData('enabled');
        $this->model->getConfig()->setData('add_widgets', true);
        $html = $this->model->getElementHtml();
        $this->assertRegExp('/.*mage\/adminhtml\/wysiwyg\/widget.*/i', $html);
    }

    public function testIsEnabled()
    {
        $this->assertEmpty($this->model->isEnabled());

        $this->model->setData('wysiwyg', true);
        $this->assertTrue($this->model->isEnabled());

        $this->model->unsetData('wysiwyg');
        $this->model->getConfig()->setData('enabled', true);
        $this->assertTrue($this->model->isEnabled());
    }

    public function testIsHidden()
    {
        $this->assertEmpty($this->model->isHidden());

        $this->model->getConfig()->setData('hidden', true);
        $this->assertTrue($this->model->isHidden());
    }

    public function testTranslate()
    {
        $this->assertEquals('Insert Image...', $this->model->translate('Insert Image...'));
    }

    public function testGetConfig()
    {
        $config = new \Magento\Framework\Object();
        $this->assertEquals($config, $this->model->getConfig());

        $this->model->getConfig()->setData('test', 'test');
        $this->assertEquals('test', $this->model->getConfig('test'));
    }

    /**
     * Test protected `getTranslatedString` method via public `getElementHtml` method
     */
    public function testGetTranslatedString()
    {
        $this->model->getConfig()->setData('enabled', true);
        $html = $this->model->getElementHtml();
        $this->assertRegExp('/.*"Insert Image...":"Insert Image...".*/i', $html);
    }
}
