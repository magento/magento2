<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Email\Test\Unit\Block\Adminhtml\Template\Render;

use Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Type;
use Magento\Framework\DataObject;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TypeTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var MockObject|Type
     */
    protected $block;

    /**
     * Setup environment
     */
    protected function setUp(): void
    {
        $this->block = $this->createPartialMockWithReflection(
            Type::class,
            ['__']
        );
    }

    /**
     * Test render() with supported template Text type
     */
    public function testRenderWithSupportedTemplateTextType()
    {
        $testCase = [
            'dataset' => [
                'template_type' => '1'
            ],
            'expectedResult' => 'Text'
        ];
        $this->executeTestCase($testCase);
    }

    /**
     * Test render() with supported template HTML type
     */
    public function testRenderWithSupportedTemplateHtmlType()
    {
        $testCase = [
            'dataset' => [
                'template_type' => '2'
            ],
            'expectedResult' => 'HTML'
        ];
        $this->executeTestCase($testCase);
    }

    /**
     * Test render() with unsupported template type
     */
    public function testRenderWithUnsupportedTemplateType()
    {
        $testCase = [
            'dataset' => [
                'template_type' => '5'
            ],
            'expectedResult' => 'Unknown'
        ];
        $this->executeTestCase($testCase);
    }

    /**
     * Execute Test case
     *
     * @param array $testCase
     */
    public function executeTestCase($testCase)
    {
        $actualResult = $this->block->render(
            new DataObject(
                [
                    'template_type' => $testCase['dataset']['template_type'],
                ]
            )
        );
        $this->assertEquals(new Phrase($testCase['expectedResult']), $actualResult);
    }
}
