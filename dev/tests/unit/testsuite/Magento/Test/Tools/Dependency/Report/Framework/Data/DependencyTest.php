<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Test\Tools\Dependency\Report\Framework\Data;

use Magento\TestFramework\Helper\ObjectManager;

class DependencyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $lib
     * @param int $count
     * @return \Magento\Tools\Dependency\Report\Framework\Data\Dependency
     */
    protected function createDependency($lib, $count)
    {
        $objectManagerHelper = new ObjectManager($this);
        return $objectManagerHelper->getObject(
            'Magento\Tools\Dependency\Report\Framework\Data\Dependency',
            array('lib' => $lib, 'count' => $count)
        );
    }

    public function testGetLib()
    {
        $lib = 'lib';

        $dependency = $this->createDependency($lib, 0);

        $this->assertEquals($lib, $dependency->getLib());
    }

    public function testGetCount()
    {
        $count = 3;

        $dependency = $this->createDependency('lib', $count);

        $this->assertEquals($count, $dependency->getCount());
    }
}
