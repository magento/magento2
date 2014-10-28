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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Indexer\Product\Eav\Plugin;

class AttributeSetTest extends \PHPUnit_Framework_TestCase
{
    public function testAroundSave()
    {
        $eavProcessorMock = $this->getMockBuilder('Magento\Catalog\Model\Indexer\Product\Eav\Processor')
            ->disableOriginalConstructor()
            ->getMock();
        $eavProcessorMock->expects($this->once())
            ->method('markIndexerAsInvalid');

        $filter = $this->getMockBuilder(
            'Magento\Catalog\Model\Indexer\Product\Eav\Plugin\AttributeSet\IndexableAttributeFilter'
        )
            ->disableOriginalConstructor()
            ->getMock();
        $filter->expects($this->at(0))
            ->method('filter')
            ->will($this->returnValue(array(1, 2, 3)));
        $filter->expects($this->at(1))
            ->method('filter')
            ->will($this->returnValue(array(1, 2)));

        $subjectMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\Set')
            ->disableOriginalConstructor()
            ->getMock();
        $subjectMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(11));

        $model = new \Magento\Catalog\Model\Indexer\Product\Eav\Plugin\AttributeSet(
            $eavProcessorMock,
            $filter
        );

        $closure  = function () use ($subjectMock) {
            return $subjectMock;
        };

        $this->assertEquals(
            $subjectMock,
            $model->aroundSave($subjectMock, $closure)
        );
    }
}
