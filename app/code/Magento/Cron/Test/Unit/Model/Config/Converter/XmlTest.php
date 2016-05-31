<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Test\Unit\Model\Config\Converter;

class XmlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cron\Model\Config\Converter\Xml
     */
    protected $_converter;

    /**
     * Initialize parameters
     */
    protected function setUp()
    {
        $this->_converter = new \Magento\Cron\Model\Config\Converter\Xml();
    }

    /**
     * Testing wrong data incoming
     */
    public function testConvertWrongIncomingData()
    {
        $result = $this->_converter->convert(['wrong data']);
        $this->assertEmpty($result);
    }

    /**
     * Testing not existing of node <job>
     */
    public function testConvertNoElements()
    {
        $result = $this->_converter->convert(new \DOMDocument());
        $this->assertEmpty($result);
    }

    /**
     * Testing converting valid cron configuration
     */
    public function testConvert()
    {
        $expected = [
            'default' => [
                'job1' => [
                    'name' => 'job1',
                    'schedule' => '30 2 * * *',
                    'instance' => 'Model1',
                    'method' => 'method1',
                ],
                'job2' => [
                    'name' => 'job2',
                    'schedule' => '* * * * *',
                    'instance' => 'Model2',
                    'method' => 'method2',
                ],
                'job3' => [
                    'name'        => 'job3',
                    'instance'    => 'Model3',
                    'method'      => 'method3',
                    'config_path' => 'some/config/path',
                ],
            ],
        ];

        $xmlFile = __DIR__ . '/../_files/crontab_valid.xml';
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($xmlFile));
        $result = $this->_converter->convert($dom);

        $this->assertEquals($expected, $result);
    }

    /**
     * Testing converting not valid cron configuration, expect to get exception
     *
     * @expectedException \InvalidArgumentException
     */
    public function testConvertWrongConfiguration()
    {
        $xmlFile = __DIR__ . '/../_files/crontab_invalid.xml';
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($xmlFile));
        $this->_converter->convert($dom);
    }
}
