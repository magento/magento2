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
namespace Magento\Cron\Model\Groups\Config\Converter;

class XmlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Cron\Model\Groups\Config\Converter\Xml
     */
    protected $object;

    public function setUp()
    {
        $this->object = new \Magento\Cron\Model\Groups\Config\Converter\Xml();
    }

    public function testConvert()
    {
        $xmlExample = <<<XML
<config>
    <group id="test">
        <schedule_generate_every>1</schedule_generate_every>
    </group>
</config>
XML;

        $xml = new \DOMDocument();
        $xml->loadXML($xmlExample);

        $results = $this->object->convert($xml);
        $this->assertArrayHasKey('test', $results);
        $this->assertArrayHasKey('schedule_generate_every', $results['test']);
        $this->assertEquals('1', $results['test']['schedule_generate_every']);
    }
}
