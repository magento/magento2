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
namespace Magento\View\Layout\File\FileList;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\View\Layout\File\FileList\Factory
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = $this->getMockForAbstractClass('Magento\ObjectManager');
        $this->model = new \Magento\View\Layout\File\FileList\Factory($this->objectManager);
    }

    public function testCreate()
    {
        $helperObjectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $collator = $helperObjectManager->getObject(\Magento\View\Layout\File\FileList\Factory::FILE_LIST_COLLATOR);
        $list = $helperObjectManager->getObject('Magento\View\Layout\File\FileList');

        $this->objectManager->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            $this->equalTo(\Magento\View\Layout\File\FileList\Factory::FILE_LIST_COLLATOR)
        )->will(
            $this->returnValue($collator)
        );

        $this->objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo('Magento\View\Layout\File\FileList'),
            $this->equalTo(array('collator' => $collator))
        )->will(
            $this->returnValue($list)
        );
        $this->assertSame($list, $this->model->create());
    }

    /**
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessage Magento\View\Layout\File\FileList\Collator has to implement the collate interface.
     */
    public function testCreateException()
    {
        $collator = new \stdClass();

        $this->objectManager->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            $this->equalTo(\Magento\View\Layout\File\FileList\Factory::FILE_LIST_COLLATOR)
        )->will(
            $this->returnValue($collator)
        );

        $this->model->create();
    }
}
