<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Tests for \Magento\Framework\Data\Form\Element\Checkbox
 */
namespace Magento\Framework\Data\Test\Unit\Form\Element;

use Magento\Framework\Data\Form\Element\Checkbox;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CheckboxTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var Checkbox
     */
    protected $_model;

    protected function setUp(): void
    {
        $factoryMock = $this->createMock(Factory::class);
        $collectionFactoryMock = $this->createMock(CollectionFactory::class);
        $escaperMock = $this->createMock(Escaper::class);
        $this->_model = new Checkbox(
            $factoryMock,
            $collectionFactoryMock,
            $escaperMock
        );
        $formMock = new DataObject();
        $formMock->getHtmlIdPrefix('id_prefix');
        $formMock->getHtmlIdPrefix('id_suffix');
        $this->_model->setForm($formMock);
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\Checkbox::__construct
     */
    public function testConstruct()
    {
        $this->assertEquals('checkbox', $this->_model->getType());
        $this->assertEquals('checkbox', $this->_model->getExtType());
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\Checkbox::getHtmlAttributes
     */
    public function testGetHtmlAttributes()
    {
        $this->assertEmpty(
            array_diff(
                ['type', 'title', 'class', 'style', 'checked', 'onclick', 'onchange', 'disabled', 'tabindex'],
                $this->_model->getHtmlAttributes()
            )
        );
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\Checkbox::getElementHtml
     */
    public function testGetElementHtml()
    {
        $this->_model->setIsChecked(false);
        $html = $this->_model->getElementHtml();
        $this->assertStringContainsString('type="checkbox"', $html);
        $this->assertStringNotContainsString('checked="checked"', $html);
        $this->_model->setIsChecked(true);
        $html = $this->_model->getElementHtml();
        $this->assertStringContainsString('checked="checked"', $html);
    }
}
