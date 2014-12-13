<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Data\Argument\Interpreter;

class ConstantTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Constant
     */
    private $object;

    protected function setUp()
    {
        $this->object = new Constant();
    }

    public function testEvaluate()
    {
        // it is defined in framework/bootstrap.php
        $this->assertEquals(TESTS_TEMP_DIR, $this->object->evaluate(['value' => 'TESTS_TEMP_DIR']));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Constant name is expected.
     * @dataProvider evaluateBadValueDataProvider
     */
    public function testEvaluateBadValue($value)
    {
        $this->object->evaluate($value);
    }

    /**
     * @return array
     */
    public function evaluateBadValueDataProvider()
    {
        return [
            [['value' => 'KNOWINGLY_UNDEFINED_CONSTANT']],
            [['value' => '']],
            [[]]
        ];
    }
}
