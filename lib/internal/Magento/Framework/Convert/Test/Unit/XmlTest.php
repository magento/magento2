<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Convert\Test\Unit;

class XmlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Convert\Xml
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = new \Magento\Framework\Convert\Xml();
    }

    public function testXmlToAssoc()
    {
        $xmlstr = $this->getXml();
        $result = $this->_model->xmlToAssoc(new \SimpleXMLElement($xmlstr));
        $this->assertEquals(
            [
                'one' => '1',
                'two' => ['three' => '3', 'four'  => '4'],
                'five' => [0 => '5', 1  => '6'],
            ],
            $result
        );
    }

    /**
     * @return string
     */
    protected function getXml()
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<_><one>1</one><two><three>3</three><four>4</four></two><five><five>5</five><five>6</five></five></_>
XML;
    }
}
