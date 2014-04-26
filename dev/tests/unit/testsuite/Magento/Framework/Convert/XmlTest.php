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
namespace Magento\Framework\Convert;

class XmlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Convert\Xml
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Framework\Convert\Xml();
    }

    public function testXmlToAssoc()
    {
        $xmlstr = $this->getXml();
        $result = $this->_model->xmlToAssoc(new \SimpleXMLElement($xmlstr));
        $this->assertEquals(
            array(
                'one' => '1',
                'two' => array('three' => '3', 'four'  => '4'),
                'five' => array(0 => '5', 1  => '6'),
            ),
            $result
        );
    }

    protected function getXml()
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<_><one>1</one><two><three>3</three><four>4</four></two><five><five>5</five><five>6</five></five></_>
XML;
    }
}
