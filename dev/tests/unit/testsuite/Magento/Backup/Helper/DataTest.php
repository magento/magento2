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
 * @category    Magento
 * @package     Magento_Backup
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Backup\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    public function testInvalidateIndexer()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $process = $this->getMockBuilder('Magento\Index\Model\Process')
            ->disableOriginalConstructor()
            ->getMock();
        $process->expects($this->once())
            ->method('changeStatus')
            ->with(\Magento\Index\Model\Process::STATUS_REQUIRE_REINDEX);
        $iterator = $this->returnValue(new \ArrayIterator(array($process)));

        $collection = $this->getMockBuilder('Magento\Index\Model\Resource\Process\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->at(0))->method('getIterator')
            ->will($iterator);

        $processFactory = $this->getMockBuilder('Magento\Index\Model\Resource\Process\CollectionFactory')
            ->setMethods(array('create'))
            ->disableOriginalConstructor()
            ->getMock();
        $processFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($collection));

        $object = $helper->getObject('Magento\Backup\Helper\Data', array(
            'processFactory' => $processFactory
        ));
        $object->invalidateIndexer();
    }
}
