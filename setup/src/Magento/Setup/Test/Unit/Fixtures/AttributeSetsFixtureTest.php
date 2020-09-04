<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Fixtures;

use Magento\Setup\Fixtures\AttributeSet\AttributeSetFixture;
use Magento\Setup\Fixtures\AttributeSet\Pattern;
use Magento\Setup\Fixtures\AttributeSetsFixture;
use Magento\Setup\Fixtures\FixtureModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD)
 */
class AttributeSetsFixtureTest extends TestCase
{
    /**
     * @var MockObject|FixtureModel
     */
    private $fixtureModelMock;

    /**
     * @var AttributeSetsFixture
     */
    private $model;

    /**
     * @var MockObject
     */
    private $attributeSetsFixtureMock;

    /**
     * @var MockObject
     */
    private $patternMock;

    protected function setUp(): void
    {
        $this->fixtureModelMock = $this->getMockBuilder(FixtureModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeSetsFixtureMock = $this->getMockBuilder(AttributeSetFixture::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->patternMock = $this->getMockBuilder(Pattern::class)
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
            ->willReturnMap($valueMap);

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
            ->with(AttributeSetsFixture::PRODUCT_SET_NAME . 1, 2, 3, $closure)
            ->willReturn(['some-data']);
        $this->attributeSetsFixtureMock->expects($this->once())
            ->method('createAttributeSet')
            ->with(['some-data']);
        $this->fixtureModelMock
            ->expects($this->exactly(4))
            ->method('getValue')
            ->willReturnMap($valueMap);

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
