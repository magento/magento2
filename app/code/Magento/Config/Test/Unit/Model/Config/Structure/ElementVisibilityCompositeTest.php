<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Structure;

use Magento\Config\Model\Config\Structure\ElementVisibilityComposite;
use Magento\Config\Model\Config\Structure\ElementVisibilityInterface;
use PHPUnit\Framework\MockObject\Matcher\InvokedCount;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ElementVisibilityCompositeTest extends TestCase
{
    /**
     * @var ElementVisibilityComposite
     */
    private $model;

    /**
     * @var ElementVisibilityInterface|MockObject
     */
    private $firstVisibilityMock;

    /**
     * @var ElementVisibilityInterface|MockObject
     */
    private $secondVisibilityMock;

    protected function setUp(): void
    {
        $this->firstVisibilityMock = $this->createMock(ElementVisibilityInterface::class);
        $this->secondVisibilityMock = $this->createMock(ElementVisibilityInterface::class);

        $this->model = new ElementVisibilityComposite([$this->firstVisibilityMock, $this->secondVisibilityMock]);
    }

    /**
     * @codingStandardsIgnoreStart
     * @codingStandardsIgnoreEnd
     */
    public function testException()
    {
        $this->expectException('Magento\Framework\Exception\ConfigurationMismatchException');
        $this->expectExceptionMessage(sprintf(
            'stdClass: Instance of %s, got stdClass instead',
            'Magento\Config\Model\Config\Structure\ElementVisibilityInterface is expected'
        ));
        $visibility = [
            'stdClass' => new \stdClass()
        ];

        new ElementVisibilityComposite($visibility);
    }

    /**
     * @param string $firstExpects
     * @param bool $firstResult
     * @param string $secondExpects
     * @param bool $secondResult
     * @param bool $expectedResult
     */
    #[DataProvider('visibilityDataProvider')]
    public function testDisabled($firstExpects, $firstResult, $secondExpects, $secondResult, $expectedResult)
    {
        $path = 'some/path';
        // Convert string expects to actual matcher
        $firstMatcher = $this->{$firstExpects}();
        $secondMatcher = $this->{$secondExpects}();
        
        $this->firstVisibilityMock->expects($firstMatcher)
            ->method('isDisabled')
            ->with($path)
            ->willReturn($firstResult);
        $this->secondVisibilityMock->expects($secondMatcher)
            ->method('isDisabled')
            ->with($path)
            ->willReturn($secondResult);

        $this->assertSame($expectedResult, $this->model->isDisabled($path));
    }

    /**
     * @param string $firstExpects
     * @param bool $firstResult
     * @param string $secondExpects
     * @param bool $secondResult
     * @param bool $expectedResult
     */
    #[DataProvider('visibilityDataProvider')]
    public function testHidden($firstExpects, $firstResult, $secondExpects, $secondResult, $expectedResult)
    {
        $path = 'some/path';
        // Convert string expects to actual matcher
        $firstMatcher = $this->{$firstExpects}();
        $secondMatcher = $this->{$secondExpects}();
        
        $this->firstVisibilityMock->expects($firstMatcher)
            ->method('isHidden')
            ->with($path)
            ->willReturn($firstResult);
        $this->secondVisibilityMock->expects($secondMatcher)
            ->method('isHidden')
            ->with($path)
            ->willReturn($secondResult);

        $this->assertSame($expectedResult, $this->model->isHidden($path));
    }

    /**
     * @return array
     */
    public static function visibilityDataProvider()
    {
        return [
            ['once', false, 'once', false, false],
            ['once', false, 'once', true, true],
            ['once', true, 'never', true, true],
            ['once', true, 'never', false, true],
        ];
    }
}
