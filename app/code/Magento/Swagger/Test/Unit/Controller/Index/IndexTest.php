<?php
/***
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swagger\Test\Unit\Controller\Index;

class IndexTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $pageConfigMock = $this->getMockBuilder('Magento\Framework\View\Page\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $resultPageFactory = $this->getMockBuilder('Magento\Framework\View\Result\PageFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $pageConfigMock->expects($this->once())->method('addBodyClass')->with('swagger-section');
        $resultPageFactory->expects($this->once())->method('create');

        $model = $objectManager->getObject(
            'Magento\Swagger\Controller\Index\Index',
            [
                'pageConfig' => $pageConfigMock,
                'pageFactory' => $resultPageFactory
            ]
        );
        $model->execute();
    }
}
