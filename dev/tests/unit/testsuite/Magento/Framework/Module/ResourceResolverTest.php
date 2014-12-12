<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
        $this->_moduleReaderMock = $this->getMock('Magento\Framework\Module\Dir\Reader', [], [], '', false);
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
                [
                    ['data', $moduleName, __DIR__ . '/_files/Module/data'],
                    ['sql', $moduleName, __DIR__ . '/_files/Module/sql'],
                ]
            )
        );

        $expectedResult = ['module_first_setup', 'module_second_setup'];
        $this->assertEquals($expectedResult, array_values($this->_model->getResourceList($moduleName)));
    }
}
