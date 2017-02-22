<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DocumentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Search\Document |\PHPUnit_Framework_MockObject_MockObject
     */
    private $document;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $documentFields = [];
        for ($count = 0; $count < 5; $count++) {
            $field = $this->getMockBuilder('Magento\Framework\Search\DocumentField')
                ->disableOriginalConstructor()
                ->getMock();

            $field->expects($this->any())->method('getName')->will($this->returnValue("$count"));
            $field->expects($this->any())->method('getValue')->will($this->returnValue($count));
            $documentFields[] = $field;
        }

        $this->document = $helper->getObject(
            'Magento\Framework\Search\Document',
            [
                'documentId' => 42,
                'documentFields' => $documentFields,
            ]
        );
    }

    public function testGetIterator()
    {
        $count = 0;
        foreach ($this->document as $field) {
            $this->assertEquals($field->getName(), "$count");
            $this->assertEquals($field->getValue(), $count);
            $count++;
        }
    }

    public function testGetFieldNames()
    {
        $this->assertEquals(
            $this->document->getFieldNames(),
            ['0', '1', '2', '3', '4']
        );
    }

    public function testGetField()
    {
        $field = $this->document->getField('3');
        $this->assertEquals($field->getValue(), 3);
    }
}
