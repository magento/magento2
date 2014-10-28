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
namespace Magento\Catalog\Model\Indexer\Product\Price\Plugin;

class CustomerGroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Plugin\CustomerGroup
     */
    protected $_model;

    /**
     * @var \Magento\Customer\Service\V1\CustomerGroupServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_subjectMock;

    public function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_subjectMock = $this->getMock(
            '\Magento\Customer\Service\V1\CustomerGroupServiceInterface', array(), array(), '', false
        );

        $indexerMock = $this->getMock(
            'Magento\Indexer\Model\Indexer',
            array('getId', 'invalidate'),
            array(),
            '',
            false
        );
        $indexerMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $indexerMock->expects($this->once())->method('invalidate');

        $this->_model = $this->_objectManager->getObject(
            '\Magento\Catalog\Model\Indexer\Product\Price\Plugin\CustomerGroup',
            array('indexer' => $indexerMock)
        );
    }

    public function testAroundDelete()
    {
        $this->assertEquals('return_value', $this->_model->afterDeleteGroup($this->_subjectMock, 'return_value'));
    }

    public function testAroundCreate()
    {
        $this->assertEquals('return_value', $this->_model->afterCreateGroup($this->_subjectMock, 'return_value'));
    }

    public function testAroundUpdate()
    {
        $this->assertEquals('return_value', $this->_model->afterUpdateGroup($this->_subjectMock, 'return_value'));
    }
}
