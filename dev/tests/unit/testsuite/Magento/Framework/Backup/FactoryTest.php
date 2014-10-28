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
namespace Magento\Framework\Backup;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Backup\Factory
     */
    protected $_model;

    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = $this->getMock('Magento\Framework\ObjectManager');
        $this->_model = new \Magento\Framework\Backup\Factory($this->_objectManager);
    }

    /**
     * @expectedException \Magento\Framework\Exception
     */
    public function testCreateWrongType()
    {
        $this->_model->create('WRONG_TYPE');
    }

    /**
     * @param string $type
     * @dataProvider allowedTypesDataProvider
     */
    public function testCreate($type)
    {
        $this->_objectManager->expects($this->once())->method('create')->will($this->returnValue('ModelInstance'));

        $this->assertEquals('ModelInstance', $this->_model->create($type));
    }

    /**
     * @return array
     */
    public function allowedTypesDataProvider()
    {
        return array(array('db'), array('snapshot'), array('filesystem'), array('media'), array('nomedia'));
    }
}
