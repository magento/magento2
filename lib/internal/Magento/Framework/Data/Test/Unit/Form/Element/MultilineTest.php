<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Test\Unit\Form\Element;

use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Multiline;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Framework\Data\Form\Element\Multiline
 */
class MultilineTest extends TestCase
{
    /** @var ObjectManager */
    private $objectManager;

    /**
     * @var Multiline
     */
    protected $element;

    /**
     * @var Factory|MockObject
     */
    protected $elementFactory;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $collectionFactory;

    /**
     * @var Escaper|MockObject
     */
    protected $escaper;

    protected function setUp(): void
    {
        $this->elementFactory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = new ObjectManager($this);

        $this->escaper = $this->objectManager->getObject(
            Escaper::class
        );

        $this->element = new Multiline(
            $this->elementFactory,
            $this->collectionFactory,
            $this->escaper
        );
    }

    /**
     * @param mixed $value
     * @param int $index
     * @param string $resultValue
     * @return void
     * @dataProvider dataProviderValues
     */
    public function testGetEscapedValue($value, $index, $resultValue)
    {
        $this->element->setValue($value);

        $result = $this->element->getEscapedValue($index);
        $this->assertEquals($resultValue, $result);
    }

    /**
     * @return array
     */
    public function dataProviderValues()
    {
        return [
            ["", 0, ""],
            ["string1", 0, "string1"],
            ["string1\nstring2", 0, "string1"],
            ["string1\nstring2", 1, "string2"],
            ["string1\nstring2", 2, null],
            [null, 0, null],
        ];
    }
}
