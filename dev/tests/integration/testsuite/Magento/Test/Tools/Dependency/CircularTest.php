<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Test\Tools\Dependency;

use Magento\Tools\Dependency\Circular;

class CircularTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\Dependency\Circular
     */
    protected $circular;

    protected function setUp()
    {
        $this->circular = new Circular();
    }

    public function testBuildCircularDependencies()
    {
        $dependencies = [1 => [2], 2 => [3, 5], 3 => [1], 5 => [2]];
        $expectedCircularDependencies = [
            1 => [[1, 2, 3, 1]],
            2 => [[2, 3, 1, 2], [2, 5, 2]],
            3 => [[3, 1, 2, 3]],
            5 => [[5, 2, 5]],
        ];
        $this->assertEquals($expectedCircularDependencies, $this->circular->buildCircularDependencies($dependencies));
    }
}
