<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Test\Unit\Form\Element;

/**
 * Tests for \Magento\Framework\Data\Form\Element\Label
 */
class LabelTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    private $objectManager;

    /**
     * @var \Magento\Framework\Data\Form\Element\Label
     */
    protected $_label;

    protected function setUp(): void
    {
        $factoryMock = $this->createMock(\Magento\Framework\Data\Form\Element\Factory::class);
        $collectionFactoryMock = $this->createMock(\Magento\Framework\Data\Form\Element\CollectionFactory::class);
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $escaper = $this->objectManager->getObject(
            \Magento\Framework\Escaper::class
        );
        $this->_label = new \Magento\Framework\Data\Form\Element\Label(
            $factoryMock,
            $collectionFactoryMock,
            $escaper
        );
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\Label::__construct
     */
    public function testConstruct()
    {
        $this->assertEquals('label', $this->_label->getType());
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\Label::getElementHtml
     */
    public function testGetElementHtml()
    {
        $this->_label->setValue('Label Text');
        $html = $this->_label->getElementHtml();
        $this->assertStringContainsString("<div class=\"control-value\">Label Text", $html);
        $this->_label->setBold(true);
        $html = $this->_label->getElementHtml();
        $this->assertStringContainsString("<div class=\"control-value special\">Label Text", $html);
    }
}
