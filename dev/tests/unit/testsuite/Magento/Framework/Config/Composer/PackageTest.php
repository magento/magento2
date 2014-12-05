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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Config\Composer;

class PackageTest extends \PHPUnit_Framework_TestCase
{
    const SAMPLE_DATA =
        '{"foo":"1","bar":"2","baz":["3","4"],"nested":{"one":"5","two":"6",
        "magento/theme-adminhtml-backend":7, "magento/theme-frontend-luma":8}}';

    /**
     * @var \StdClass
     */
    private $sampleJson;

    /**
     * @var Package
     */
    private $object;

    protected function setUp()
    {
        $this->sampleJson = json_decode(self::SAMPLE_DATA);
        $this->object = new Package($this->sampleJson);
    }

    public function testGetJson()
    {
        $this->assertInstanceOf('\StdClass', $this->object->getJson(false));
        $this->assertEquals($this->sampleJson, $this->object->getJson(false));
        $this->assertSame($this->sampleJson, $this->object->getJson(false));
        $this->assertEquals(
            json_encode($this->sampleJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n",
            $this->object->getJson(true, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    public function testGet()
    {
        $this->assertSame('1', $this->object->get('foo'));
        $this->assertSame(['3', '4'], $this->object->get('baz'));
        $nested = $this->object->get('nested');
        $this->assertInstanceOf('\StdClass', $nested);
        $this->assertObjectHasAttribute('one', $nested);
        $this->assertEquals('5', $nested->one);
        $this->assertEquals('5', $this->object->get('nested->one'));
        $this->assertObjectHasAttribute('two', $nested);
        $this->assertEquals('6', $nested->two);
        $this->assertEquals('6', $this->object->get('nested->two'));
        $this->assertEquals(
            ['magento/theme-adminhtml-backend' => 7, 'magento/theme-frontend-luma' => 8],
            (array)$this->object->get('nested', '/^magento\/theme/')
            );
    }
}
