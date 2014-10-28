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
namespace Magento\Framework\Module;

class ResourceResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var  \Magento\Framework\Module\ResourceResolver
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_moduleReaderMock;

    protected function setUp()
    {
        $this->_moduleReaderMock = $this->getMock('Magento\Framework\Module\Dir\Reader', array(), array(), '', false);
        $this->_model = new \Magento\Framework\Module\ResourceResolver($this->_moduleReaderMock);
    }

    public function testGetResourceList()
    {
        $moduleName = 'Module';
        $this->_moduleReaderMock->expects(
            $this->any()
        )->method(
            'getModuleDir'
        )->will(
            $this->returnValueMap(
                array(
                    array('data', $moduleName, __DIR__ . '/_files/Module/data'),
                    array('sql', $moduleName, __DIR__ . '/_files/Module/sql')
                )
            )
        );


        $expectedResult = array('module_first_setup', 'module_second_setup');
        $this->assertEquals($expectedResult, array_values($this->_model->getResourceList($moduleName)));
    }
}
