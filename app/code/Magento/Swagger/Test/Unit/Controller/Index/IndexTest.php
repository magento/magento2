<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Swagger\Test\Unit\Controller\Index;

class IndexTest extends \PHPUnit\Framework\TestCase
{
    public function testExecute()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $pageConfigMock = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultPageFactory = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pageConfigMock->expects($this->once())->method('addBodyClass')->with('swagger-section');
        $resultPageFactory->expects($this->once())->method('create');

        $model = $objectManager->getObject(
            \Magento\Swagger\Controller\Index\Index::class,
            [
                'pageConfig' => $pageConfigMock,
                'pageFactory' => $resultPageFactory
            ]
        );
        $model->execute();
    }
}
