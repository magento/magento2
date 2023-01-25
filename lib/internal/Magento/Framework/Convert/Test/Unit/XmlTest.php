<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Convert\Test\Unit;

use Magento\Framework\Convert\Xml;
use PHPUnit\Framework\TestCase;

class XmlTest extends TestCase
{
    /**
     * @var Xml
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = new Xml();
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
