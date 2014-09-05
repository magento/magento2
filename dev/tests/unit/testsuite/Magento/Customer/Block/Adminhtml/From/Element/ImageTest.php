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
namespace Magento\Customer\Block\Adminhtml\From\Element;

/**
 * Test class for \Magento\Customer\Block\Adminhtml\From\Element\Image
 */
class ImageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Block\Adminhtml\Form\Element\Image
     */
    protected $image;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $backendHelperMock;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->backendHelperMock = $this->getMockBuilder('Magento\Backend\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $this->image = $objectManager->getObject(
            'Magento\Customer\Block\Adminhtml\Form\Element\Image',
            ['adminhtmlData' => $this->backendHelperMock]
        );
    }

    public function testGetPreviewFile()
    {
        $value = 'image.jpg';
        $url = 'http://example.com/backend/customer/index/viewfile/' . $value;
        $formMock = $this->getMockBuilder('Magento\Framework\Data\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $this->image->setForm($formMock);
        $this->image->setValue($value);

        $this->backendHelperMock->expects($this->once())
            ->method('urlEncode')
            ->with($value)
            ->will($this->returnArgument(0));
        $this->backendHelperMock->expects($this->once())
            ->method('getUrl')
            ->with('customer/index/viewfile', ['image' => $value])
            ->will($this->returnValue($url));

        $this->assertContains($url, $this->image->getElementHtml());
    }
}
