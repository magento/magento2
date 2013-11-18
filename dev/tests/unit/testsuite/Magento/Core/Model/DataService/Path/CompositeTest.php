<?php
/**
 * \Magento\Core\Model\DataService\Path\Composite
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\DataService\Path;

class CompositeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Names to use for testing path composite
     */
    const ITEM_ONE = 'ITEM_ONE';
    const ITEM_TWO = 'ITEM_TWO';
    const ITEM_THREE = 'ITEM_THREE';

    /** @var \Magento\Core\Model\DataService\Path\Composite */
    protected $_composite;

    /**
     * object map for mock object manager
     * @var array
     */
    protected $_map;

    protected function setUp()
    {
        /** @var $objectManagerMock \Magento\ObjectManager */
        $objectManagerMock = $this->getMockBuilder('Magento\ObjectManager')->disableOriginalConstructor()->getMock();
        $this->_map = array(
            array(self::ITEM_ONE, (object)array('name' => self::ITEM_ONE)),
            array(self::ITEM_TWO, (object)array('name' => self::ITEM_TWO)),
            array(self::ITEM_THREE, (object)array('name' => self::ITEM_THREE))
        );
        $objectManagerMock->expects($this->any())->method('get')->will($this->returnValueMap($this->_map));
        $vector = array((self::ITEM_ONE)   => (self::ITEM_ONE),
                        (self::ITEM_TWO)   => (self::ITEM_TWO),
                        (self::ITEM_THREE) => (self::ITEM_THREE));
        $this->_composite
            = new \Magento\Core\Model\DataService\Path\Composite($objectManagerMock, $vector);
    }

    /**
     * @dataProvider childrenProvider
     */
    public function testGetChildNode($elementName, $expectedResult)
    {
        $child = $this->_composite->getChildNode($elementName);

        $this->assertEquals($expectedResult, $child);
    }

    public function childrenProvider()
    {
        return array(
            // elementName, expectedResult
            array(self::ITEM_ONE, (object)array('name' => self::ITEM_ONE)),
            array(self::ITEM_TWO, (object)array('name' => self::ITEM_TWO)),
            array(self::ITEM_THREE, (object)array('name' => self::ITEM_THREE)),
            array('none', null),
        );
    }
}
