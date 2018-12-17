<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Plugin\Framework\App\Action;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Catalog\Plugin\Framework\App\Action\ContextPlugin;
use Magento\Catalog\Model\Product\ProductList\ToolbarMemorizer;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Framework\App\Http\Context as HttpContext;

/**
 * Class for testing ContextPlugin class.
 */
class ContextPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ContextPlugin
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ToolbarMemorizer
     */
    private $toolbarMemorizerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CatalogSession
     */
    private $catalogSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|HttpContext
     */
    private $httpContextMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->toolbarMemorizerMock = $this->getMockBuilder(ToolbarMemorizer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->catalogSessionMock = $this->getMockBuilder(CatalogSession::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->httpContextMock = $this->getMockBuilder(HttpContext::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            ContextPlugin::class,
            [
                'toolbarMemorizer' => $this->toolbarMemorizerMock,
                'catalogSession' => $this->catalogSessionMock,
                'httpContext' => $this->httpContextMock,
            ]
        );
    }

    /**
     * Test beforeDispatch method.
     *
     * @return void
     */
    public function testBeforeDispatch()
    {
        $this->toolbarMemorizerMock->method('isMemorizingAllowed')->willReturn(true);
        $this->catalogSessionMock->method('getData')->willReturn('any_value');

        $this->model->beforeDispatch();
    }
}
