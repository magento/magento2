<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\System\Writer;

require_once realpath(
    __DIR__ . '/../../../../../../../../'
) . '/tools/Magento/Tools/Migration/System/Writer/Factory.php';
require_once realpath(
    __DIR__ . '/../../../../../../../../'
) . '/tools/Magento/Tools/Migration/System/Writer/FileSystem.php';
class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\Migration\System\Writer\Factory
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Tools\Migration\System\Writer\Factory();
    }

    public function testGetWriterReturnsProperWriter()
    {
        $this->assertInstanceOf('Magento\Tools\Migration\System\Writer\FileSystem', $this->_model->getWriter('write'));
        $this->assertInstanceOf(
            'Magento\Tools\Migration\System\Writer\Memory',
            $this->_model->getWriter('someWriter')
        );
    }
}
