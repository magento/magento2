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
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once realpath(dirname(__FILE__) . '/../../../../../../../')
    . '/tools/migration/System/Configuration/Generator.php';
require_once realpath(dirname(__FILE__) . '/../../../../../../../')
    . '/tools/migration/System/FileManager.php';
require_once realpath(dirname(__FILE__) . '/../../../../../../../')
    . '/tools/migration/System/Configuration/LoggerAbstract.php';
require_once realpath(dirname(__FILE__) . '/../../../../../../../')
    . '/tools/migration/System/Configuration/Formatter.php';


class Tools_Migration_System_Configuration_GeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Tools_Migration_System_Configuration_Generator
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fileManagerMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_loggerMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_formatterMock;

    protected function setUp()
    {
        $this->_fileManagerMock = $this->getMock('Tools_Migration_System_FileManager', array(), array(), '', false);
        $this->_loggerMock = $this->getMockForAbstractClass('Tools_Migration_System_Configuration_LoggerAbstract',
            array(), '', false, false, false, array('add')
        );
        $this->_formatterMock = $this->getMock('Tools_Migration_System_Configuration_Formatter', array(), array(),
            '', false
        );

        $this->_model = new Tools_Migration_System_Configuration_Generator(
            $this->_formatterMock, $this->_fileManagerMock, $this->_loggerMock
        );
    }

    public function testCreateConfigurationGeneratesConfiguration()
    {
        $dom = new DOMDocument();
        $dom->loadXML(
            preg_replace('/\n|\s{4}/', '', file_get_contents(__DIR__ . '/_files/convertedConfiguration.xml'))
        );
        $stripComments = new DOMXPath($dom);
        foreach ($stripComments->query('//comment()') as $comment) {
            $comment->parentNode->removeChild($comment);
        }
        $dom->formatOutput = true;
        $dom->preserveWhiteSpace = false;
        $expectedXml = $dom->saveXML();

        $this->_fileManagerMock->expects($this->once())->method('write')
        ->with($this->stringContains('system.xml'), $expectedXml);

        $this->_formatterMock->expects($this->once())->method('parseString')
            ->will(
                $this->returnCallback(
                    function($xml) {
                        $dom = new DOMDocument();
                        $dom->loadXML($xml);
                        $dom->preserveWhiteSpace = false;
                        $dom->formatOutput = true;
                        return $dom->saveXML();
                    }
                )
            );

        $this->_loggerMock->expects($this->once())->method('add')->with(
            'someFile',
            Tools_Migration_System_Configuration_LoggerAbstract:: FILE_KEY_VALID
        );

        $this->_model->createConfiguration('someFile', include __DIR__ . '/_files/mappedConfiguration.php');
    }
}
