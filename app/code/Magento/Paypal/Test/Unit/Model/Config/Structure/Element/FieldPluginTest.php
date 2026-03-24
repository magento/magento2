<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model\Config\Structure\Element;

use Magento\Config\Model\Config\Structure\Element\Field as FieldConfigStructureMock;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Paypal\Model\Config\Structure\Element\FieldPlugin as FieldConfigStructurePlugin;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FieldPluginTest extends TestCase
{
    /**
     * @var FieldConfigStructurePlugin
     */
    private $plugin;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var FieldConfigStructureMock|MockObject
     */
    private $subjectMock;

    protected function setUp(): void
    {
        $this->subjectMock = $this->createMock(FieldConfigStructureMock::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->plugin = $this->objectManagerHelper->getObject(
            FieldConfigStructurePlugin::class
        );
    }

    public function testAroundGetConfigPathHasResult()
    {
        $someResult = 'some result';

        $this->assertEquals($someResult, $this->plugin->afterGetConfigPath($this->subjectMock, $someResult));
    }

    public function testAroundGetConfigPathNonPaymentSection()
    {
        $this->subjectMock->expects($this->once())
            ->method('getPath')
            ->willReturn('non-payment/group/field');

        $this->assertNull($this->plugin->afterGetConfigPath($this->subjectMock, null));
    }

    /**
     * @param string $subjectPath
     * @param string $expectedConfigPath
     */
    #[DataProvider('afterGetConfigPathDataProvider')]
    public function testAroundGetConfigPath($subjectPath, $expectedConfigPath)
    {
        $this->subjectMock->expects($this->exactly(2))
            ->method('getPath')
            ->willReturn($subjectPath);

        $this->assertEquals($expectedConfigPath, $this->plugin->afterGetConfigPath($this->subjectMock, null));
    }

    /**
     * @return array
     */
    public static function afterGetConfigPathDataProvider()
    {
        return [
            ['payment_us/group/field', 'payment/group/field'],
            ['payment_other/group/field', 'payment/group/field'],
            ['payment_us', 'payment_us'],
            ['payment_wrong_country/group/field', 'payment_wrong_country/group/field']
        ];
    }
}
