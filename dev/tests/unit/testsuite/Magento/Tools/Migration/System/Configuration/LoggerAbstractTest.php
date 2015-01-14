<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\System\Configuration;

require_once realpath(
    __DIR__ . '/../../../../../../../../'
) . '/tools/Magento/Tools/Migration/System/Configuration/AbstractLogger.php';
class LoggerAbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\Migration\System\Configuration\AbstractLogger
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = $this->getMockForAbstractClass('Magento\Tools\Migration\System\Configuration\AbstractLogger');
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    /**
     * @covers \Magento\Tools\Migration\System\Configuration\AbstractLogger::add()
     * @covers \Magento\Tools\Migration\System\Configuration\AbstractLogger::__toString()
     */
    public function testToString()
    {
        $this->_model->add('file1', \Magento\Tools\Migration\System\Configuration\AbstractLogger::FILE_KEY_VALID);
        $this->_model->add('file2', \Magento\Tools\Migration\System\Configuration\AbstractLogger::FILE_KEY_INVALID);

        $expected = 'valid: 1' .
            PHP_EOL .
            'invalid: 1' .
            PHP_EOL .
            'Total: 2' .
            PHP_EOL .
            '------------------------------' .
            PHP_EOL .
            'valid:' .
            PHP_EOL .
            'file1' .
            PHP_EOL .
            '------------------------------' .
            PHP_EOL .
            'invalid:' .
            PHP_EOL .
            'file2';

        $this->assertEquals($expected, (string)$this->_model);
    }
}
