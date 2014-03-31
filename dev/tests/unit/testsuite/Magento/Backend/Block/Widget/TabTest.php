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
namespace Magento\Backend\Block\Widget;

class TabTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $helper;

    protected function setUp()
    {
        $this->helper = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    /**
     * @param string $method
     * @param string $field
     * @param mixed $value
     * @param mixed $expected
     * @dataProvider dataProvider
     */
    public function testGetters($method, $field, $value, $expected)
    {
        /** @var \Magento\Backend\Block\Widget\Tab $object */
        $object = $this->helper->getObject(
            '\Magento\Backend\Block\Widget\Tab',
            array('data' => array($field => $value))
        );
        $this->assertEquals($expected, $object->{$method}());
    }

    public function dataProvider()
    {
        return array(
            'getTabLabel' => array('getTabLabel', 'label', 'test label', 'test label'),
            'getTabLabel (default)' => array('getTabLabel', 'empty', 'test label', null),
            'getTabTitle' => array('getTabTitle', 'title', 'test title', 'test title'),
            'getTabTitle (default)' => array('getTabTitle', 'empty', 'test title', null),
            'canShowTab' => array('canShowTab', 'can_show', false, false),
            'canShowTab (default)' => array('canShowTab', 'empty', false, true),
            'isHidden' => array('isHidden', 'is_hidden', true, true),
            'isHidden (default)' => array('isHidden', 'empty', true, false),
            'getTabClass' => array('getTabClass', 'class', 'test classes', 'test classes'),
            'getTabClass (default)' => array('getTabClass', 'empty', 'test classes', null),
            'getTabUrl' => array('getTabUrl', 'url', 'test url', 'test url'),
            'getTabUrl (default)' => array('getTabUrl', 'empty', 'test url', '#')
        );
    }
}
