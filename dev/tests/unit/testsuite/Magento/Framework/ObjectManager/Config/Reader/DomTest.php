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

namespace Magento\Framework\ObjectManager\Config\Reader;

require_once __DIR__ . '/_files/ConfigDomMock.php';

class DomTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileResolverMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $converterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $schemaLocatorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $validationStateMock;

    /**
     * @var \Magento\Framework\ObjectManager\Config\Reader\Dom
     */
    protected $model;

    protected function setUp()
    {
        $this->fileResolverMock = $this->getMock('\Magento\Framework\Config\FileResolverInterface');
        $this->converterMock = $this->getMock(
            '\Magento\Framework\ObjectManager\Config\Mapper\Dom',
            array(),
            array(),
            '',
            false
        );
        $this->schemaLocatorMock = $this->getMock(
            '\Magento\Framework\ObjectManager\Config\SchemaLocator',
            array(),
            array(),
            '',
            false
        );
        $this->validationStateMock = $this->getMock(
            '\Magento\Framework\Config\ValidationStateInterface',
            array(),
            array(),
            '',
            false
        );

        $this->model = new \Magento\Framework\ObjectManager\Config\Reader\Dom(
            $this->fileResolverMock,
            $this->converterMock,
            $this->schemaLocatorMock,
            $this->validationStateMock,
            'filename.xml',
            array(),
            '\ConfigDomMock'
        );
    }

    /**
     * @covers \Magento\Framework\ObjectManager\Config\Reader\Dom::_createConfigMerger()
     */
    public function testRead()
    {
        $fileList = array('first content item');
        $this->fileResolverMock->expects($this->once())->method('get')->will($this->returnValue($fileList));
        $this->converterMock->expects($this->once())->method('convert')->with('reader dom result');
        $this->model->read();
    }
}
