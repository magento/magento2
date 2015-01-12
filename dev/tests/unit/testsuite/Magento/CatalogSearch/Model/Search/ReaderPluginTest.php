<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Search;

class ReaderPluginTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\CatalogSearch\Model\Search\RequestGenerator|\PHPUnit_Framework_MockObject_MockObject */
    protected $requestGenerator;
    /** @var \Magento\TestFramework\Helper\ObjectManager */
    protected $objectManagerHelper;
    /** @var \Magento\CatalogSearch\Model\Search\ReaderPlugin */
    protected $object;

    public function setUp()
    {
        $this->requestGenerator = $this->getMockBuilder('Magento\\CatalogSearch\\Model\\Search\\RequestGenerator')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->object = $this->objectManagerHelper->getObject(
            'Magento\\CatalogSearch\\Model\\Search\\ReaderPlugin',
            ['requestGenerator' => $this->requestGenerator]
        );
    }

    public function testAroundRead()
    {
        $this->requestGenerator->expects($this->once())
            ->method('generate')
            ->will($this->returnValue(['test' => 'a']));

        $result = $this->object->aroundRead(
            $this->getMockBuilder('Magento\Framework\Config\ReaderInterface')->disableOriginalConstructor()->getMock(),
            function () {
                return ['test' => 'b', 'd' => 'e'];
            }
        );

        $this->assertEquals(['test' => ['b', 'a'], 'd' => 'e'], $result);
    }
}
