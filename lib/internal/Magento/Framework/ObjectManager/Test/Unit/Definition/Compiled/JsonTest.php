<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Test\Unit\Definition\Compiled;

use Magento\Framework\Json\JsonInterface;

class JsonTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    private $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    public function testGetParametersWithoutDefinition()
    {
        $signatures = [];
        $definitions = ['wonderful' => null];
        $model = new \Magento\Framework\ObjectManager\Definition\Compiled\Json([$signatures, $definitions]);
        $this->assertEquals(null, $model->getParameters('wonderful'));
    }

    public function testGetParametersWithSignatureObject()
    {
        $wonderfulSignature = new \stdClass();
        $signatures = ['wonderfulClass' => $wonderfulSignature];
        $definitions = ['wonderful' => 'wonderfulClass'];
        $model = new \Magento\Framework\ObjectManager\Definition\Compiled\Json([$signatures, $definitions]);
        $this->assertEquals($wonderfulSignature, $model->getParameters('wonderful'));
    }

    public function testGetParametersWithUnpacking()
    {
        $checkString = 'code to pack';
        $signatures = ['wonderfulClass' => json_encode($checkString)];
        $definitions = ['wonderful' => 'wonderfulClass'];
        $object = new \Magento\Framework\ObjectManager\Definition\Compiled\Json([$signatures, $definitions]);
        $jsonMock = $this->getMock(JsonInterface::class);
        $jsonMock->method('encode')
            ->willReturnCallback(function ($string) {
                return json_decode($string, true);
            });
        $jsonMock->method('decode')
            ->willReturnCallback(function ($data) {
                return json_decode($data, true);
            });
        $this->objectManagerHelper->setBackwardCompatibleProperty(
            $object,
            'json',
            $jsonMock
        );
        $this->assertEquals($checkString, $object->getParameters('wonderful'));
    }
}
