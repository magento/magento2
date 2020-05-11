<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Tests for \Magento\Framework\Data\Form\Element\Submit
 */
namespace Magento\Framework\Data\Test\Unit\Form\Element;

use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Submit;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SubmitTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var Submit
     */
    protected $_model;

    protected function setUp(): void
    {
        $factoryMock = $this->createMock(Factory::class);
        $collectionFactoryMock = $this->createMock(CollectionFactory::class);
        $escaperMock = $this->createMock(Escaper::class);
        $this->_model = new Submit(
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
     * @covers \Magento\Framework\Data\Form\Element\Submit::__construct
     */
    public function testConstruct()
    {
        $this->assertEquals('submit', $this->_model->getType());
        $this->assertEquals('submit', $this->_model->getExtType());
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\Submit::getHtml
     */
    public function testGetHtml()
    {
        $html = $this->_model->getHtml();
        $this->assertStringContainsString('type="submit"', $html);
        $this->assertGreaterThan(0, preg_match('/class=\".*submit.*\"/i', $html));
    }
}
