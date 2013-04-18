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
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_DesignEditor_Model_HistoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_DesignEditor_Model_History
     */
    protected $_historyObject;

    /**
     * Get clear history model
     *
     * @return Mage_DesignEditor_Model_History
     */
    protected function getClearHistoryModel()
    {
        return $this->_historyObject = Mage::getModel('Mage_DesignEditor_Model_History');
    }

    /**
     * Add change test
     *
     * @dataProvider getChange
     */
    public function testAddChange($change)
    {
        $historyModel = $this->getClearHistoryModel();
        $collection = $historyModel->addChange($change)->getChanges();

        $this->assertEquals(array($change), $collection->toArray());
    }

    /**
     * Add change with invalid data test
     *
     * @dataProvider getInvalidChange
     * @expectedException Magento_Exception
     */
    public function testAddChangeWithInvalidData($change)
    {
        $historyModel = $this->getClearHistoryModel();
        $historyModel->addChange($change)->getChanges();
    }

    /**
     * Set changes test
     *
     * @dataProvider getChanges
     */
    public function testSetChanges($changes)
    {
        $historyModel = $this->getClearHistoryModel();
        $collection = $historyModel->setChanges($changes)->getChanges();

        $this->assertEquals($changes, $collection->toArray());
    }

    /**
     * Test output(renderer)
     *
     * @dataProvider getChanges
     */
    public function testOutput($changes)
    {
        $historyModel = $this->getClearHistoryModel();
        /** @var $layoutRenderer Mage_DesignEditor_Model_History_Renderer_LayoutUpdate */
        $layoutRenderer = Mage::getModel('Mage_DesignEditor_Model_History_Renderer_LayoutUpdate');

        /** @var $collection Mage_DesignEditor_Model_Change_Collection */
        $collection = $historyModel->setChanges($changes)->getChanges();

        /** @var $historyCompactModel Mage_DesignEditor_Model_History_Compact */
        $historyCompactModel = Mage::getModel('Mage_DesignEditor_Model_History_Compact');
        $historyCompactModel->compact($collection);

        $this->assertXmlStringEqualsXmlFile(
            realpath(__DIR__) . '/../_files/history/layout_renderer.xml', $historyModel->output($layoutRenderer)
        );
    }

    /**
     * Add Xml changes test
     *
     * @dataProvider getXmlChanges
     * @param string $changes
     * @param array $result
     */
    public function testAddXmlChanges($changes, $result)
    {
        $historyModel = $this->getClearHistoryModel();
        $collection = $historyModel->addXmlChanges($changes)->getChanges();

        $this->assertEquals($result, $collection->toArray());

    }

    /**
     * Get change
     *
     * @return array
     */
    public function getChange()
    {
        return array(array(
            array(
                'handle'                => 'customer_account',
                'type'                  => 'layout',
                'element_name'          => 'customer_account_navigation',
                'action_name'           => 'move',
                'destination_container' => 'content',
                'destination_order'     => '-',
                'origin_container'      => 'top.menu',
                'origin_order'          => '-'
            ),
        ));
    }

    /**
     * Get invalid change
     *
     * @return array
     */
    public function getInvalidChange()
    {
        return array(array(
            array(
                'handle'                => 'customer_account',
                'type'                  => '',
                'element_name'          => '',
                'action_name'           => 'move',
                'destination_container' => 'content',
                'destination_order'     => '-',
                'origin_container'      => 'top.menu',
                'origin_order'          => '-'
            ),
        ));
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
                'handle'                => 'customer_account',
                'type'                  => 'layout',
                'element_name'          => 'customer_account_navigation',
                'action_name'           => 'move',
                'destination_container' => 'content',
                'destination_order'     => '-',
                'origin_container'      => 'top.menu',
                'origin_order'          => '-'
            ),
            array(
                'handle'                => 'customer_account',
                'type'                  => 'layout',
                'element_name'          => 'customer_account_navigation',
                'action_name'           => 'move',
                'destination_container' => 'right',
                'destination_order'     => '-',
                'origin_container'      => 'content',
                'origin_order'          => '-'
            ),
            array(
                'handle'                => 'catalog_category_view',
                'type'                  => 'layout',
                'element_name'          => 'category.products',
                'action_name'           => 'move',
                'destination_container' => 'content',
                'destination_order'     => '-',
                'origin_container'      => 'right',
                'origin_order'          => '-'
            ),
            array(
                'handle'                => 'catalog_category_view',
                'type'                  => 'layout',
                'element_name'          => 'category.products',
                'action_name'           => 'remove',
            ),
        )));
    }

    /**
     * Get xml changes data provider
     *
     * @return array
     */
    public function getXmlChanges()
    {
        return array(
            array(
                'xml' => '<move element="customer_account_navigation" after="-" destination="right"/>',
                'expected result' => array(array(
                        'element_name'          => 'customer_account_navigation',
                        'destination_order'     => '-',
                        'origin_order'          => '-',
                        'destination_container' => 'right',
                        'origin_container'      => 'right',
                        'type'                  => 'layout',
                        'action_name'           => 'move',
                        'element'               => 'customer_account_navigation',
                        'after'                 => '-',
                        'destination'           => 'right',
                        'id'                    => 'customer_account_navigation',
                        'system'                => '1'
                ))
            ),
            array(
                'xml' => '<remove name="category.products"/>',
                'expected result' => array(array(
                        'element_name' => 'category.products',
                        'type'         => 'layout',
                        'action_name'  => 'remove',
                        'name'         => 'category.products',
                        'id'           => 'category.products',
                        'system'       => '1'
                ))
            ),
            array(
                'xml' => '<move element="customer_account_navigation" after="-" destination="right"/>
                    <remove name="category.products"/>',
                'expected result' => array(
                    array(
                        'element_name'          => 'customer_account_navigation',
                        'destination_order'     => '-',
                        'origin_order'          => '-',
                        'destination_container' => 'right',
                        'origin_container'      => 'right',
                        'type'                  => 'layout',
                        'action_name'           => 'move',
                        'element'               => 'customer_account_navigation',
                        'after'                 => '-',
                        'destination'           => 'right',
                        'id'                    => 'customer_account_navigation',
                        'system'                => '1'
                    ),
                    array(
                        'element_name' => 'category.products',
                        'type'         => 'layout',
                        'action_name'  => 'remove',
                        'name'         => 'category.products',
                        'id'           => 'category.products',
                        'system'       => '1'
                    )
                )

            )
        );
    }
}
