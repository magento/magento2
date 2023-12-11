<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Tests for \Magento\Framework\Data\Form\Element\Note
 */
namespace Magento\Framework\Data\Test\Unit\Form\Element;

use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Note;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NoteTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var Note
     */
    protected $_model;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $factoryMock = $this->createMock(Factory::class);
        $collectionFactoryMock = $this->createMock(CollectionFactory::class);
        $escaperMock = $objectManager->getObject(Escaper::class);
        $this->_model = new Note(
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
     * @covers \Magento\Framework\Data\Form\Element\Note::__construct
     */
    public function testConstruct()
    {
        $this->assertEquals('note', $this->_model->getType());
    }

    /**
     * @covers \Magento\Framework\Data\Form\Element\Note::getElementHtml
     */
    public function testGetElementHtml()
    {
        $this->_model->setBeforeElementHtml('note_before');
        $this->_model->setAfterElementHtml('note_after');
        $this->_model->setId('note_id');
        $this->_model->setData('ui_id', 'ui_id');
        $this->_model->setValue('Note Text');
        $html = $this->_model->getElementHtml();
        $this->assertEquals(
            "note_before<div id=\"note_id\" class=\"control-value admin__field-value\"></div>note_after",
            $html
        );
    }
}
