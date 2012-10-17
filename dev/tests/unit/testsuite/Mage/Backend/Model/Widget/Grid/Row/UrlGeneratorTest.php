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
 * @package     Mage_Backend
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Backend_Model_Widget_Grid_Row_UrlGeneratorTest extends PHPUnit_Framework_TestCase
{
    public function testGetUrl()
    {
        $itemId = 3;
        $urlPath = 'mng/item/edit';

        $itemMock = $this->getMock('Varien_Object', array('getItemId'), array(), '', false);
        $itemMock->expects($this->once())
            ->method('getItemId')
            ->will($this->returnValue($itemId));

        $urlModelMock = $this->getMock('Mage_Backend_Model_Url', array(), array(), '', false);
        $urlModelMock->expects($this->once())
            ->method('getUrl')
            ->will($this->returnValue('http://localhost/' . $urlPath . '/flag/1/item_id/' . $itemId));

        $model = new Mage_Backend_Model_Widget_Grid_Row_UrlGenerator(array(
            'urlModel' => $urlModelMock,
            'path' => $urlPath,
            'params' => array('flag' => 1),
            'extraParamsTemplate' => array('item_id' => 'getItemId')
        ));

        $url = $model->getUrl($itemMock);

        $this->assertContains($urlPath, $url);
        $this->assertContains('flag/1', $url);
        $this->assertContains('item_id/' . $itemId, $url);
    }
}
