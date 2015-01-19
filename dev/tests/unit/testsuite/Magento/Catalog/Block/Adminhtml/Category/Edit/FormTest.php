<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Category\Edit;

/**
 * Class FormTest
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Adminhtml\Category\Edit\Form
     */
    protected $form;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Backend\Block\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Catalog\Model\Resource\Category\Tree|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryTreeMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryFactoryMock;

    /**
     * @var \Magento\Framework\Json\EncoderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $jsonEncoderMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->contextMock = $this->getMock(
            'Magento\Backend\Block\Template\Context',
            [],
            [],
            '',
            false
        );
        $this->categoryTreeMock = $this->getMock(
            'Magento\Catalog\Model\Resource\Category\Tree',
            [],
            [],
            '',
            false
        );
        $this->registryMock = $this->getMock(
            'Magento\Framework\Registry',
            [],
            [],
            '',
            false
        );
        $this->categoryFactoryMock = $this->getMock(
            'Magento\Catalog\Model\CategoryFactory',
            [],
            [],
            '',
            false
        );
        $this->jsonEncoderMock = $this->getMockForAbstractClass(
            'Magento\Framework\Json\EncoderInterface',
            [],
            '',
            false
        );
        $this->requestMock = $this->getMockForAbstractClass(
            'Magento\Framework\App\RequestInterface',
            [],
            '',
            false,
            true,
            true,
            ['getParam']
        );

        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->requestMock));

        $this->form = $this->objectManager->getObject(
            'Magento\Catalog\Block\Adminhtml\Category\Edit\Form',
            [
                'context' => $this->contextMock,
                'categoryTree' => $this->categoryTreeMock,
                'registry' => $this->registryMock,
                'categoryFactory' => $this->categoryFactoryMock,
                'jsonEncoder' => $this->jsonEncoderMock,
            ]
        );
    }

    /**
     * Run test getParentCategoryId method
     *
     * @return int
     */
    public function testGetParentCategoryId()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('parent')
            ->will($this->returnValue(123));

        $this->assertEquals(123, $this->form->getParentCategoryId());
    }

    /**
     * Run test getCategoryId method
     *
     * @return int
     */
    public function testGetCategoryId()
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('id')
            ->will($this->returnValue(789));

        $this->assertEquals(789, $this->form->getCategoryId());
    }
}
