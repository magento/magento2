<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Test\Unit\Definition;

use Magento\Framework\Serialize\SerializerInterface;

class CompiledTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    private $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    /**
     * @param array $signatures
     * @param array $definitions
     * @param mixed $expected
     * @dataProvider getParametersDataProvider
     */
    public function testGetParametersWithoutDefinition($signatures, $definitions, $expected)
    {
        $model = new \Magento\Framework\ObjectManager\Definition\Compiled([$signatures, $definitions]);
        $this->assertEquals($expected, $model->getParameters('wonderful'));
    }

    public function getParametersDataProvider()
    {
        $wonderfulSignature = new \stdClass();
        return [
            [
                [],
                ['wonderful' => null],
                null,
            ],
            [
                ['wonderfulClass' => $wonderfulSignature],
                ['wonderful' => 'wonderfulClass'],
                $wonderfulSignature,
            ]
        ];
    }

    public function testGetParametersWithUnpacking()
    {
        $checkString = 'code to pack';
        $signatures = ['wonderfulClass' => json_encode($checkString)];
        $definitions = ['wonderful' => 'wonderfulClass'];
        $object = new \Magento\Framework\ObjectManager\Definition\Compiled([$signatures, $definitions]);
        $serializerMock = $this->getMock(SerializerInterface::class);
        $serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturnCallback(function ($data) {
                return json_decode($data, true);
            });
        $this->objectManagerHelper->setBackwardCompatibleProperty(
            $object,
            'serializer',
            $serializerMock
        );
        $this->assertEquals($checkString, $object->getParameters('wonderful'));
    }
}
