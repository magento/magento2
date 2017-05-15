<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures;

use Magento\Setup\Fixtures\AttributeSet\AttributeSetFixture;
use Magento\Setup\Fixtures\AttributeSetsFixture;

/**
 * @SuppressWarnings(PHPMD)
 */
class AttributeSetsFixtureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Fixtures\FixtureModel
     */
    private $fixtureModelMock;

    /**
     * @var \Magento\Setup\Fixtures\AttributeSetsFixture
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeSetsFixtureMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $patternMock;

    public function setUp()
    {
        $this->fixtureModelMock = $this->getMockBuilder(\Magento\Setup\Fixtures\FixtureModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeSetsFixtureMock = $this->getMockBuilder(AttributeSetFixture::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->patternMock = $this->getMockBuilder(\Magento\Setup\Fixtures\AttributeSet\Pattern::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new AttributeSetsFixture(
            $this->fixtureModelMock,
            $this->attributeSetsFixtureMock,
            $this->patternMock
        );
    }

    public function testCreateAttributeSet()
    {
        $valueMap = [
            ['attribute_sets', null, ['attribute_set' => [['some-data']]]],
            ['product_attribute_sets', null, null],
        ];

        $this->attributeSetsFixtureMock->expects($this->once())
            ->method('createAttributeSet')
            ->with(['some-data']);
        $this->fixtureModelMock
            ->expects($this->exactly(2))
            ->method('getValue')
            ->will($this->returnValueMap($valueMap));

        $this->model->execute();
    }

    public function testCreateProductAttributeSet()
    {
        $valueMap = [
            ['attribute_sets', null, null],
            ['product_attribute_sets', null, 1],
            ['product_attribute_sets_attributes', 3, 2],
            ['product_attribute_sets_attributes_values', 3, 3],
        ];

        $closure = function () {
        };
        $this->patternMock->expects($this->once())
            ->method('generateAttributeSet')
            ->with(\Magento\Setup\Fixtures\AttributeSetsFixture::PRODUCT_SET_NAME . 1, 2, 3, $closure)
            ->willReturn(['some-data']);
        $this->attributeSetsFixtureMock->expects($this->once())
            ->method('createAttributeSet')
            ->with(['some-data']);
        $this->fixtureModelMock
            ->expects($this->exactly(4))
            ->method('getValue')
            ->will($this->returnValueMap($valueMap));

        $this->model->execute();
    }

    public function testGetActionTitle()
    {
        $this->assertSame('Generating attribute sets', $this->model->getActionTitle());
    }

    public function testIntroduceParamLabels()
    {
        $this->assertSame([
            'attribute_sets' => 'Attribute Sets (Default)',
            'product_attribute_sets' => 'Attribute Sets (Extra)'
        ], $this->model->introduceParamLabels());
    }
}
