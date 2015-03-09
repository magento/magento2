<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Test\Unit\Model\Template;

use Magento\Store\Model\StoreManagerInterface;

class FilterTest extends \PHPUnit_Framework_TestCase
{
    public function testMediaDirective()
    {
        $image = 'wysiwyg/VB.png';
        $construction = ['{{media url="' . $image . '"}}', 'media', ' url="' . $image . '"'];
        $baseUrl = 'http://localhost/pub/media/';

        /** @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject $storeMock */
        $storeMock = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $storeMock->expects($this->once())->method('getBaseUrl')->with(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
            ->willReturn($baseUrl);

        /** @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject $storeManagerMock */
        $storeManagerMock = $this->getMock('Magento\Store\Model\StoreManagerInterface', [], [], '', false);
        $storeManagerMock->expects($this->once())->method('getStore')->will($this->returnValue($storeMock));

        /** @var \Magento\Widget\Model\Template\Filter $filter */
        $filter = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject(
                'Magento\Widget\Model\Template\Filter',
                ['storeManager' => $storeManagerMock]
            );
        $result = $filter->mediaDirective($construction);
        $this->assertEquals($baseUrl . $image, $result);
    }
}
