<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\Di\Code\Scanner;

class ArrayScannerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Setup\Module\Di\Code\Scanner\ArrayScanner
     */
    protected $_model;

    /**
     * @var string
     */
    protected $_testDir;

    protected function setUp()
    {
        $this->_model = new \Magento\Setup\Module\Di\Code\Scanner\ArrayScanner();
        $this->_testDir = str_replace('\\', '/', realpath(__DIR__ . '/../../') . '/_files');
    }

    public function testCollectEntities()
    {
        $actual = $this->_model->collectEntities([$this->_testDir . '/additional.php']);
        $expected = ['Some_Model_Proxy', 'Some_Model_EntityFactory'];
        $this->assertEquals($expected, $actual);
    }
}
