<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Helper\Form;

class CategoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    public function setUp()
    {
        $this->authorization = $this->getMockBuilder('Magento\Framework\AuthorizationInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    /**
     * @dataProvider isAllowedDataProvider
     * @param $isAllowed
     */
    public function testIsAllowed($isAllowed)
    {
        $this->authorization->expects($this->any())
            ->method('isAllowed')
            ->will($this->returnValue($isAllowed));
        $model = $this->objectManager->getObject(
            'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Category',
            ['authorization' => $this->authorization]
        );
        switch ($isAllowed) {
            case true:
                $this->assertEquals('select', $model->getType());
                $this->assertNull($model->getClass());
                break;
            case false:
                $this->assertEquals('hidden', $model->getType());
                $this->assertContains('hidden', $model->getClass());
                break;
        }
    }

    public function isAllowedDataProvider()
    {
        return [
            [true],
            [false],
        ];
    }

    public function testGetAfterElementHtml()
    {
        $model = $this->objectManager->getObject(
            'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Category',
            ['authorization' => $this->authorization]
        );
        $this->authorization->expects($this->any())
            ->method('isAllowed')
            ->will($this->returnValue(false));
        $this->assertEmpty($model->getAfterElementHtml());
    }
}
