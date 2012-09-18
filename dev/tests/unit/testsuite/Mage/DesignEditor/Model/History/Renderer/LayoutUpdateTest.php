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
 * @package     Mage_DesignEditor
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_DesignEditor_Model_History_Renderer_LayoutUpdateTest extends PHPUnit_Framework_TestCase
{
    /**
     * Layout update renderer
     *
     * @var null|Mage_DesignEditor_Model_History_Renderer_LayoutUpdate
     */
    protected $_layoutRenderer;

    /**
     * Init test environment
     */
    protected function setUp()
    {
        $this->_layoutRenderer = new Mage_DesignEditor_Model_History_Renderer_LayoutUpdate;
    }

    /**
     * Test renderer
     *
     * @param array $changes
     * @dataProvider getChanges
     */
    public function testRenderer($changes)
    {
        $collection = $this->_mockCollection($changes);

        $this->assertXmlStringEqualsXmlFile(
            realpath(__DIR__) . '/../../_files/history/layout_renderer.xml', $this->_layoutRenderer->render($collection)
        );
    }

    /**
     * Get mocked object of collection
     *
     * @param array $data
     * @return Mage_DesignEditor_Model_Change_Collection|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _mockCollection(array $data)
    {
        /** @var $collectionMock Mage_DesignEditor_Model_Change_Collection */
        $collectionMock = $this->getMock(
            'Mage_DesignEditor_Model_Change_Collection', array('_init'), array(), '', true
        );
        foreach ($data as $item) {
            $changeClassName = Mage_DesignEditor_Model_Change_Factory::getClass($item);
            /** @var $itemMock Mage_DesignEditor_Model_Change_LayoutAbstract */
            $itemMock = $this->getMock($changeClassName, array('getLayoutDirective'), array(), '', false);

            $itemMock->expects($this->any())
                ->method('getLayoutDirective')
                ->will($this->returnValue($item['action_name']));

            $itemMock->setData($item);
            $collectionMock->addItem($itemMock);
        }
        return $collectionMock;
    }

    /**
     * Get changes
     *
     * @return array
     */
    public function getChanges()
    {
        return array(array(array(
            array(
                'handle'                => 'catalog_category_view',
                'type'                  => 'layout',
                'element_name'          => 'category.products',
                'action_name'           => 'move',
                'destination_container' => 'right',
                'destination_order'     => '-',
                'origin_container'      => 'content',
                'origin_order'          => '-'
            ),
            array(
                'handle'                => 'customer_account',
                'type'                  => 'layout',
                'element_name'          => 'customer_account_navigation',
                'action_name'           => 'remove',
            ),
        )));
    }
}
