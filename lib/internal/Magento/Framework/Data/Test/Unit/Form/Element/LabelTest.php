<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Test\Unit\Form\Element;

use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Label;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Tests for \Magento\Framework\Data\Form\Element\Label
 */
class LabelTest extends TestCase
{
    /** @var ObjectManager */
    private $objectManager;

    /**
     * @var Label
     */
    protected $_label;

    protected function setUp(): void
    {
        $factoryMock = $this->createMock(Factory::class);
        $collectionFactoryMock = $this->createMock(CollectionFactory::class);
        $this->objectManager = new ObjectManager($this);
        $escaper = $this->objectManager->getObject(
            Escaper::class
        );
        $this->_label = new Label(
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
