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
 * @category   Magento
 * @package    tools
 * @copyright  Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once realpath(dirname(__FILE__) . '/../../../../../../../')
    . '/tools/migration/System/Configuration/Mapper/Abstract.php';

require_once realpath(dirname(__FILE__) . '/../../../../../../../')
    . '/tools/migration/System/Configuration/Mapper.php';

require_once realpath(dirname(__FILE__) . '/../../../../../../../')
    . '/tools/migration/System/Configuration/Mapper/Tab.php';

require_once realpath(dirname(__FILE__) . '/../../../../../../../')
    . '/tools/migration/System/Configuration/Mapper/Section.php';

/**
 * Test case for Tools_Migration_System_Configuration_Mapper
 */
class Tools_Migration_System_Configuration_MapperTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Tools_Migration_System_Configuration_Mapper
     */
    protected $_object;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_tabMapperMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_sectionMapperMock;

    protected function setUp()
    {
        $this->_tabMapperMock = $this->getMock('Tools_Migration_System_Configuration_Mapper_Tab',
            array(), array(), '', false
        );
        $this->_sectionMapperMock = $this->getMock('Tools_Migration_System_Configuration_Mapper_Section',
            array(), array(), '', false
        );

        $this->_object = new Tools_Migration_System_Configuration_Mapper(
            $this->_tabMapperMock,
            $this->_sectionMapperMock
        );
    }

    protected function tearDown()
    {
        $this->_object = null;
        $this->_tabMapperMock = null;
        $this->_sectionMapperMock = null;
    }

    public function testTransformWithSetTabsAndSections()
    {
        $config = array(
            'comment' => 'test comment',
            'tabs' => array(
                'test tabs config',
            ),
            'sections' => array(
                'test sections config',
            ),
        );

        $this->_tabMapperMock->expects($this->once())->method('transform')
            ->with(array('test tabs config'))->will($this->returnArgument(0));

        $this->_sectionMapperMock->expects($this->once())->method('transform')
            ->with(array('test sections config'))->will($this->returnArgument(0));

        $expected = array(
            'comment' => 'test comment',
            'nodes' => array(
                'test tabs config',
                'test sections config',
            ),
        );
        $actual = $this->_object->transform($config);

        $this->assertEquals($expected, $actual);
    }

    public function testTransformWithoutSetTabsAndSections()
    {
        $config = array(
            'comment' => 'test comment',
        );

        $this->_tabMapperMock->expects($this->once())->method('transform')
            ->with(array())->will($this->returnArgument(0));

        $this->_sectionMapperMock->expects($this->once())->method('transform')
            ->with(array())->will($this->returnArgument(0));

        $expected = array(
            'comment' => 'test comment',
            'nodes' => array(),
        );
        $actual = $this->_object->transform($config);

        $this->assertEquals($expected, $actual);
    }
}
