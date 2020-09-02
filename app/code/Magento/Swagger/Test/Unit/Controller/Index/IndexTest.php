<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Swagger\Test\Unit\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\View\Result\PageFactory;
use Magento\Swagger\Controller\Index\Index;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    public function testExecute()
    {
        /** @var MockObject|Context $pageConfigMock */
        $contextMock = $this->createMock(Context::class);

        /** @var MockObject|PageConfig $pageConfigMock */
        $pageConfigMock = $this->getMockBuilder(PageConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var MockObject|PageFactory $resultPageFactory */
        $resultPageFactory = $this->getMockBuilder(PageFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pageConfigMock->expects($this->once())->method('addBodyClass')->with('swagger-section');
        $resultPageFactory->expects($this->once())->method('create');

        $indexAction = new Index($contextMock, $pageConfigMock, $resultPageFactory);
        $indexAction->execute();
    }
}
