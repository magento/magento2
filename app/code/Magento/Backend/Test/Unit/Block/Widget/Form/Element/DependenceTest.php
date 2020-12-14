<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Block\Widget\Form\Element;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Form\Element\Dependence;
use Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DependenceTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var EncoderInterface|MockObject
     */
    private $jsonEncoderMock;

    /**
     * @var FieldFactory|MockObject
     */
    private $fieldFactoryMock;

    /**
     * @var SecureHtmlRenderer|MockObject
     */
    private $secureHtmlRendererMock;

    /**
     * @var Dependence
     */
    private $dependence;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->jsonEncoderMock = $this->createMock(EncoderInterface::class);
        $this->fieldFactoryMock = $this->createMock(FieldFactory::class);
        $this->secureHtmlRendererMock = $this->createMock(SecureHtmlRenderer::class);

        $this->dependence = new Dependence(
            $this->contextMock,
            $this->jsonEncoderMock,
            $this->fieldFactoryMock,
            [],
            $this->secureHtmlRendererMock
        );
    }

    /**
     * @param string|array $value
     * @param array $expectedFieldValues
     *
     * @dataProvider fieldDependenceValuesDataProvider
     */
    public function testSetsFieldDependenceValuesAlwaysAsArray($value, $expectedFieldValues): void
    {
        $this->fieldFactoryMock
            ->expects(self::once())
            ->method('create')
            ->with(['fieldData' => ['values' => $expectedFieldValues], 'fieldPrefix' => '']);

        $this->dependence->addFieldDependence('test_1', 'from_1', $value);
    }

    public function fieldDependenceValuesDataProvider(): array
    {
        return [
            ['value_1', ['value_1']],
            [['value_1', 'value_2'], ['value_1', 'value_2']],
        ];
    }

    public function testSetsFieldDependenceValuesDirectlyIfTHeyAreAnObject(): void
    {
        $this->fieldFactoryMock
            ->expects(self::never())
            ->method('create');

        $this->dependence->addFieldDependence('test_1', 'from_1', new \stdClass());
    }
}
