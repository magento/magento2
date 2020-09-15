<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\System\Config\Backend\Catalog\Url\Rewrite;

use Magento\Catalog\Model\System\Config\Backend\Catalog\Url\Rewrite\Suffix;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\Framework\App\Cache\Type\Block;
use Magento\Framework\App\Cache\Type\Collection;
use Magento\Framework\App\Cache\TypeList;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Helper\UrlRewrite;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SuffixTest extends TestCase
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ManagerInterface
     */
    protected $eventDispatcher;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var ScopeConfigInterface
     */
    protected $config;

    /**
     * @var TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * @var UrlRewrite
     */
    protected $urlRewriteHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ResourceConnection
     */
    protected $appResource;

    /**
     * @var UrlFinderInterface
     */
    protected $urlFinder;

    /**
     * @var Suffix
     */
    protected $suffixModel;

    protected function setUp(): void
    {
        $this->eventDispatcher = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['dispatch'])
            ->getMockForAbstractClass();
        $this->eventDispatcher->method('dispatch')->willReturnSelf();
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEventDispatcher'])
            ->getMock();
        $this->context->method('getEventDispatcher')->willReturn($this->eventDispatcher);

        $this->registry = $this->createMock(Registry::class);
        $this->config = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->cacheTypeList = $this->getMockBuilder(TypeList::class)
            ->disableOriginalConstructor()
            ->setMethods(['invalidate'])
            ->getMock();

        $this->urlRewriteHelper = $this->getMockBuilder(UrlRewrite::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStores'])
            ->getMock();
        $this->storeManager->method('getStores')->willReturn([]);

        $this->appResource =$this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlFinder =$this->getMockBuilder(UrlFinderInterface::class)
            ->setMethods(['findAllByData', 'findOneByData'])
            ->getMockForAbstractClass();
        $this->urlFinder->method('findAllByData')->willReturn([]);

        $this->suffixModel = new Suffix(
            $this->context,
            $this->registry,
            $this->config,
            $this->cacheTypeList,
            $this->urlRewriteHelper,
            $this->storeManager,
            $this->appResource,
            $this->urlFinder
        );
    }

    public function testAfterSaveCleanCache()
    {
        $this->suffixModel->setValue('new');
        $this->suffixModel->setPath(
            CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX
        );
        $this->cacheTypeList->expects($this->exactly(2))->method('invalidate')->withConsecutive(
            [$this->equalTo([
                Block::TYPE_IDENTIFIER,
                Collection::TYPE_IDENTIFIER
            ])],
            [$this->equalTo('config')]
        );
        $this->suffixModel->afterSave();
    }

    public function testAfterSaveWithoutChanges()
    {
        $this->suffixModel->setValue('');
        $this->suffixModel->setPath(
            CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX
        );
        $this->cacheTypeList->expects($this->never())->method('invalidate');
        $this->suffixModel->afterSave();
    }

    public function testAfterSaveProduct()
    {
        $this->suffixModel->setValue('new');
        $this->suffixModel->setPath(
            ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX
        );
        $this->cacheTypeList->expects($this->once())->method('invalidate')->with('config');
        $this->suffixModel->afterSave();
    }
}
