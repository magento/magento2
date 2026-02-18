<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Block\Adminhtml\System\Config\Field\Enable;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Math\Random;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Paypal\Test\Unit\Block\Adminhtml\System\Config\Field\Enable\AbstractEnable\Stub;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Class AbstractEnableTest
 *
 * Test for class \Magento\Paypal\Block\Adminhtml\System\Config\Field\Enable\AbstractEnable
 */
class AbstractEnableTest extends TestCase
{
    use MockCreationTrait;

    private const EXPECTED_ATTRIBUTE = 'data-enable="stub"';

    /**
     * @var Stub
     */
    protected $abstractEnable;

    /**
     * @var AbstractElement|MockObject
     */
    protected $elementMock;

    /**
     * Create mock objects.
     *
     * @param string[] $classes
     * @return MockObject[]
     */
    private function createMocks(array $classes): array
    {
        $mocks = [];
        foreach ($classes as $class) {
            $mocks[] = $this->createMock($class);
        }

        return $mocks;
    }

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $randomMock = $this->createMock(Random::class);
        $randomMock->method('getRandomString')->willReturn('12345abcdef');

        $mockArguments = $this->createMocks([
            \Magento\Framework\Data\Form\Element\Factory::class,
            CollectionFactory::class,
            Escaper::class
        ]);
        $mockArguments[] = [];
        $mockArguments[] = $this->createMock(SecureHtmlRenderer::class);
        $mockArguments[] = $randomMock;

        $this->elementMock = $this->createPartialMockWithReflection(
            AbstractElement::class,
            ['getHtmlId', 'getForm', 'getTooltip']
        );

        $escaper = $objectManager->getObject(Escaper::class);
        $reflection = new ReflectionClass(AbstractElement::class);
        $reflectionProperty = $reflection->getProperty('_escaper');
        $reflectionProperty->setValue($this->elementMock, $escaper);

        $randomProperty = $reflection->getProperty('random');
        $randomProperty->setValue($this->elementMock, $randomMock);

        $objects = [
            [
                JsonHelper::class,
                $this->createMock(JsonHelper::class)
            ],
            [
                DirectoryHelper::class,
                $this->createMock(DirectoryHelper::class)
            ],
            [
                \Magento\Framework\Translate\InlineInterface::class,
                $this->createMock(\Magento\Framework\Translate\InlineInterface::class)
            ],
            [
                \Magento\Framework\ZendEscaper::class,
                $this->createMock(\Magento\Framework\ZendEscaper::class)
            ]
        ];
        $objectManager->prepareObjectManager($objects);
        $this->abstractEnable = $objectManager->getObject(
            Stub::class,
            [
                '_escaper' => $objectManager->getObject(Escaper::class)
            ]
        );
    }

    /**
     * Run test for getUiId method
     *
     * @return void
     */
    public function testGetUiId()
    {
        $this->assertStringContainsString(self::EXPECTED_ATTRIBUTE, $this->abstractEnable->getUiId());
    }

    /**
     * Run test for render method
     *
     * @return void
     */
    public function testRender()
    {
        $formMock = $this->createPartialMockWithReflection(Form::class, ['getFieldNameSuffix']);

        $this->elementMock->expects($this->any())
            ->method('getHtmlId')
            ->willReturn('test-html-id');
        $this->elementMock->expects($this->once())
            ->method('getTooltip')
            ->willReturn('');
        $this->elementMock->expects($this->any())
            ->method('getForm')
            ->willReturn($formMock);
        $formMock->expects($this->any())
            ->method('getFieldNameSuffix')
            ->willReturn('');

        $this->assertStringContainsString(self::EXPECTED_ATTRIBUTE, $this->abstractEnable->render($this->elementMock));
    }
}
