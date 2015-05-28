<?php
/***
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Ui\Component\Listing\Column;

class PageActionsTest extends \PHPUnit_Framework_TestCase
{
    public function testPrepareItemsByPageId()
    {
        // Create Mocks and SUT
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var \PHPUnit_Framework_MockObject_MockObject $urlBuilderMock */
        $urlBuilderMock = $this->getMockBuilder('Magento\Framework\UrlInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $inputUrl = 'href/url/for/edit/action';

        /** @var \Magento\Cms\Ui\Component\Listing\Column\PageActions $model */
        $model = $objectManager->getObject(
            'Magento\Cms\Ui\Component\Listing\Column\PageActions',
            [
                'urlBuilder' => $urlBuilderMock,
                'url' => $inputUrl
            ]
        );

        // Define test input and expectations
        $items = ['data' => ['items' => [['page_id' => 1]]]];
        $fullUrl = 'full-url-including-base.com/href/url/for/edit/action';
        $name = 'item_name';

        $editArray = [
            'href' => $fullUrl,
            'label' => __('Edit'),
            'hidden' => true
        ];
        $expectedItems = [
            [
                'page_id' => 1,
                $name => ['edit' => $editArray]
            ]
        ];

        // Configure mocks and object data
        $urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with($inputUrl, ['page_id' => 1])
            ->willReturn($fullUrl);

        $model->setName($name);
        $model->prepareDataSource($items);
        // Run test
        $this->assertEquals(
            $expectedItems,
            $items['data']['items']
        );
    }
}
