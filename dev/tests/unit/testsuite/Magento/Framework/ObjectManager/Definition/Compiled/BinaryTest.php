<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\ObjectManager\Definition\Compiled;

class BinaryTest extends \PHPUnit_Framework_TestCase
{
    public function testGetParametersWithUnpacking()
    {
        if (!function_exists('igbinary_serialize')) {
            $this->markTestSkipped('This test requires igbinary PHP extension');
        }
        $checkString = 'packed code';
        $signatures = ['wonderfulClass' => igbinary_serialize($checkString)];
        $definitions = ['wonderful' => 'wonderfulClass'];
        $model = new \Magento\Framework\ObjectManager\Definition\Compiled\Binary([$signatures, $definitions]);
        $this->assertEquals($checkString, $model->getParameters('wonderful'));
    }
}
