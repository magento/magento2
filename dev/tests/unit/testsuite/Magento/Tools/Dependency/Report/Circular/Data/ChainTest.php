<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Tools\Dependency\Report\Circular\Data;

use Magento\TestFramework\Helper\ObjectManager;

class ChainTest extends \PHPUnit_Framework_TestCase
{
    public function testGetModules()
    {
        $modules = ['foo', 'baz', 'bar'];

        $objectManagerHelper = new ObjectManager($this);
        /** @var \Magento\Tools\Dependency\Report\Circular\Data\Chain $chain */
        $chain = $objectManagerHelper->getObject(
            'Magento\Tools\Dependency\Report\Circular\Data\Chain',
            ['modules' => $modules]
        );

        $this->assertEquals($modules, $chain->getModules());
    }
}
