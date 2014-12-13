<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Model\Product\Option;

use Magento\TestFramework\Helper\ObjectManager;

class UrlBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Option\UrlBuilder
     */
    private $model;

    public function testGetUrl()
    {
        $this->assertEquals('testResult', $this->model->getUrl('router', []));
    }

    protected function setUp()
    {
        $mockedFrontendUrlBuilder = $this->getMockedFrontendUrlBuilder();
        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            '\Magento\Catalog\Model\Product\Option\UrlBuilder',
            ['frontendUrlBuilder' => $mockedFrontendUrlBuilder]
        );
    }

    /**
     * @return \Magento\Framework\UrlInterface
     */
    private function getMockedFrontendUrlBuilder()
    {
        $mockBuilder = $this->getMockBuilder('\Magento\Framework\UrlInterface')
            ->disableOriginalConstructor();
        $mock = $mockBuilder->getMockForAbstractClass();

        $mock->expects($this->any())
            ->method('getUrl')
            ->will($this->returnValue('testResult'));

        return $mock;
    }
}
