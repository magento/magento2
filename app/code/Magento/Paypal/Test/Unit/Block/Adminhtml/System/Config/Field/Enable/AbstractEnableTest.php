<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Block\Adminhtml\System\Config\Field\Enable;

use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Paypal\Test\Unit\Block\Adminhtml\System\Config\Field\Enable\AbstractEnable\Stub;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractEnableTest
 *
 * Test for class \Magento\Paypal\Block\Adminhtml\System\Config\Field\Enable\AbstractEnable
 */
class AbstractEnableTest extends TestCase
{
    const EXPECTED_ATTRIBUTE = 'data-enable="stub"';

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
            $mocks[] = $this->getMockBuilder($class)->disableOriginalConstructor()->getMock();
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

        $randomMock = $this->getMockBuilder(Random::class)->disableOriginalConstructor()->getMock();
        $randomMock->method('getRandomString')->willReturn('12345abcdef');
        $mockArguments = $this->createMocks([
            \Magento\Framework\Data\Form\Element\Factory::class,
            CollectionFactory::class,
            Escaper::class
        ]);
        $mockArguments[] = [];
        $mockArguments[] = $this->createMock(SecureHtmlRenderer::class);
        $mockArguments[] = $randomMock;
        $this->elementMock = $this->getMockBuilder(AbstractElement::class)
            ->setMethods(
                [
                    'getHtmlId',
                    'getTooltip',
                    'getForm'
                ]
            )->setConstructorArgs($mockArguments)
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);
        $escaper = $objectManager->getObject(Escaper::class);
        $reflection = new \ReflectionClass($this->elementMock);
        $reflection_property = $reflection->getProperty('_escaper');
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($this->elementMock, $escaper);

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
        $formMock = $this->getMockBuilder(Form::class)
            ->setMethods(['getFieldNameSuffix'])
            ->disableOriginalConstructor()
            ->getMock();

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
