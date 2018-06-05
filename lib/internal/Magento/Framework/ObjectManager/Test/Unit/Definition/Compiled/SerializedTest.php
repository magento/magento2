<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Test\Unit\Definition\Compiled;

class SerializedTest extends \PHPUnit_Framework_TestCase
{
    public function testGetParametersWithoutDefinition()
    {
        $signatures = [];
        $definitions = ['wonderful' => null];
        $model = new \Magento\Framework\ObjectManager\Definition\Compiled\Serialized([$signatures, $definitions]);
        $this->assertEquals(null, $model->getParameters('wonderful'));
    }

    public function testGetParametersWithSignatureObject()
    {
        $wonderfulSignature = new \stdClass();
        $signatures = ['wonderfulClass' => $wonderfulSignature];
        $definitions = ['wonderful' => 'wonderfulClass'];
        $model = new \Magento\Framework\ObjectManager\Definition\Compiled\Serialized([$signatures, $definitions]);
        $this->assertEquals($wonderfulSignature, $model->getParameters('wonderful'));
    }

    public function testGetParametersWithUnpacking()
    {
        $checkString = 'code to pack';
        $signatures = ['wonderfulClass' => serialize($checkString)];
        $definitions = ['wonderful' => 'wonderfulClass'];
        $model = new \Magento\Framework\ObjectManager\Definition\Compiled\Serialized([$signatures, $definitions]);
        $this->assertEquals($checkString, $model->getParameters('wonderful'));
    }
}
