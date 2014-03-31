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
 * @category    Magento
 * @package     Magento_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\View\Layout;

class ElementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\View\Layout\Element
     */
    protected $_model;

    public function testPrepare()
    {
        /**
         * @TODO: Need to use ObjectManager instead 'new'.
         * On this moment we have next bug MAGETWO-4274 which blocker for this key.
         */
        $this->_model = new \Magento\View\Layout\Element(__DIR__ . '/_files/_layout_update.xml', 0, true);

        list($blockNode) = $this->_model->xpath('//block[@name="head"]');
        list($actionNode) = $this->_model->xpath('//action[@method="setTitle"]');

        $this->assertEmpty($blockNode->attributes()->parent);
        $this->assertEmpty($actionNode->attributes()->block);

        $this->_model->prepare();

        $this->assertEquals('root', (string)$blockNode->attributes()->parent);
        $this->assertEquals('Magento\Backend\Block\Page\Head', (string)$blockNode->attributes()->class);
        $this->assertEquals('head', (string)$actionNode->attributes()->block);
    }
}
