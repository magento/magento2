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

/**
 * Test class for \Magento\Framework\View\Layout\Element
 */
namespace Magento\Framework\View\Layout;

class ElementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider elementNameDataProvider
     */
    public function testGetElementName($xml, $name)
    {
        $model = new \Magento\Framework\View\Layout\Element($xml);
        $this->assertEquals($name, $model->getElementName());
    }

    public function elementNameDataProvider()
    {
        return array(
            array('<block name="name" />', 'name'),
            array('<container name="name" />', 'name'),
            array('<referenceBlock name="name" />', 'name'),
            array('<invalid name="name" />', false),
            array('<block />', '')
        );
    }

    public function cacheableDataProvider()
    {
        return array(
            array('<containter name="name" />', true),
            array('<block name="name" cacheable="false" />', false),
            array('<block name ="bl1"><block name="bl2" /></block>', true),
            array('<block name ="bl1"><block name="bl2" cacheable="false"/></block>', false),
            array('<block name="name" />', true),
            array('<renderer cacheable="false" />', true),
            array('<renderer name="name" />', true),
            array('<widget cacheable="false" />', true),
            array('<widget name="name" />', true)
        );
    }

    /**
     * @dataProvider cacheableDataProvider
     */
    public function testIsCacheable($xml, $expected)
    {
        $model = new \Magento\Framework\View\Layout\Element($xml);
        $this->assertEquals($expected, $model->isCacheable());
    }
}
