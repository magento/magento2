<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\Config\Structure\Element;

use Magento\Paypal\Model\Config\Structure\Element\FieldPlugin as FieldConfigStructurePlugin;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Config\Model\Config\Structure\Element\Field as FieldConfigStructureMock;

class FieldPluginTest extends \PHPUnit\Framework\TestCase
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
     * @var FieldConfigStructureMock|\PHPUnit\Framework\MockObject\MockObject
     */
    private $subjectMock;

    protected function setUp(): void
    {
        $this->subjectMock = $this->getMockBuilder(FieldConfigStructureMock::class)
            ->disableOriginalConstructor()
            ->getMock();

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
     *
     * @dataProvider afterGetConfigPathDataProvider
     */
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
    public function afterGetConfigPathDataProvider()
    {
        return [
            ['payment_us/group/field', 'payment/group/field'],
            ['payment_other/group/field', 'payment/group/field'],
            ['payment_us', 'payment_us'],
            ['payment_wrong_country/group/field', 'payment_wrong_country/group/field']
        ];
    }
}
