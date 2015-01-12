<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\System\Configuration;

require_once realpath(
    __DIR__ . '/../../../../../../../../'
) . '/tools/Magento/Tools/Migration/System/Configuration/Generator.php';
require_once realpath(
    __DIR__ . '/../../../../../../../../'
) . '/tools/Magento/Tools/Migration/System/FileManager.php';
require_once realpath(
    __DIR__ . '/../../../../../../../../'
) . '/tools/Magento/Tools/Migration/System/Configuration/AbstractLogger.php';
require_once realpath(
    __DIR__ . '/../../../../../../../../'
) . '/tools/Magento/Tools/Migration/System/Configuration/Formatter.php';
class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\Migration\System\Configuration\Generator
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fileManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_loggerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_formatterMock;

    protected function setUp()
    {
        $this->_fileManagerMock = $this->getMock(
            'Magento\Tools\Migration\System\FileManager',
            [],
            [],
            '',
            false
        );
        $this->_loggerMock = $this->getMockForAbstractClass(
            'Magento\Tools\Migration\System\Configuration\AbstractLogger',
            [],
            '',
            false,
            false,
            false,
            ['add']
        );
        $this->_formatterMock = $this->getMock(
            'Magento\Tools\Migration\System\Configuration\Formatter',
            [],
            [],
            '',
            false
        );

        $this->_model = new \Magento\Tools\Migration\System\Configuration\Generator(
            $this->_formatterMock,
            $this->_fileManagerMock,
            $this->_loggerMock
        );
    }

    public function testCreateConfigurationGeneratesConfiguration()
    {
        $dom = new \DOMDocument();
        $dom->loadXML(
            preg_replace('/\n|\s{4}/', '', file_get_contents(__DIR__ . '/_files/convertedConfiguration.xml'))
        );
        $stripComments = new \DOMXPath($dom);
        foreach ($stripComments->query('//comment()') as $comment) {
            $comment->parentNode->removeChild($comment);
        }
        $dom->formatOutput = true;
        $dom->preserveWhiteSpace = false;
        $expectedXml = $dom->saveXML();

        $this->_fileManagerMock->expects(
            $this->once()
        )->method(
            'write'
        )->with(
            $this->stringContains('system.xml'),
            $expectedXml
        );

        $this->_formatterMock->expects($this->once())->method('parseString')->will(
            $this->returnCallback(
                function ($xml) {
                    $dom = new \DOMDocument();
                    $dom->loadXML($xml);
                    $dom->preserveWhiteSpace = false;
                    $dom->formatOutput = true;
                    return $dom->saveXML();
                }
            )
        );

        $this->_loggerMock->expects(
            $this->once()
        )->method(
            'add'
        )->with(
            'someFile',
            \Magento\Tools\Migration\System\Configuration\AbstractLogger::FILE_KEY_INVALID
        );

        $this->_model->createConfiguration('someFile', include __DIR__ . '/_files/mappedConfiguration.php');
    }
}
