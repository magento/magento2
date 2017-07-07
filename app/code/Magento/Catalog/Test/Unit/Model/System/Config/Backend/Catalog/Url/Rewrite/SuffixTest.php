<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\System\Config\Backend\Catalog\Url\Rewrite;

class SuffixTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Model\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventDispatcher;
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $config;
    
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $cacheTypeList;
    
    /**
     * @var \Magento\UrlRewrite\Helper\UrlRewrite
     */
    protected $urlRewriteHelper;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $appResource;
    
    /**
     * @var \Magento\UrlRewrite\Model\UrlFinderInterface
     */
    protected $urlFinder;

    /**
     * @var \Magento\Catalog\Model\System\Config\Backend\Catalog\Url\Rewrite\Suffix
     */
    protected $suffixModel;

    public function setUp()
    {
        $this->eventDispatcher = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['dispatch'])
            ->getMock();
        $this->eventDispatcher->method('dispatch')->willReturnSelf();
        $this->context = $this->getMockBuilder(\Magento\Framework\Model\Context::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEventDispatcher'])
            ->getMock();
        $this->context->method('getEventDispatcher')->willReturn($this->eventDispatcher);
        
        $this->registry = $this->getMock(\Magento\Framework\Registry::class);
        $this->config = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->cacheTypeList = $this->getMockBuilder(\Magento\Framework\App\Cache\TypeList::class)
            ->disableOriginalConstructor()
            ->setMethods(['invalidate'])
            ->getMock();

        $this->urlRewriteHelper = $this->getMockBuilder(\Magento\UrlRewrite\Helper\UrlRewrite::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStores'])
            ->getMock();
        $this->storeManager->method('getStores')->willReturn([]);
        
        $this->appResource =$this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlFinder =$this->getMockBuilder(\Magento\UrlRewrite\Model\UrlFinderInterface::class)
            ->setMethods(['findAllByData', 'findOneByData'])
            ->getMock();
        $this->urlFinder->method('findAllByData')->willReturn([]);
        
        $this->suffixModel = new \Magento\Catalog\Model\System\Config\Backend\Catalog\Url\Rewrite\Suffix(
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
            \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX
        );
        $this->cacheTypeList->expects($this->exactly(2))->method('invalidate')->withConsecutive(
            [$this->equalTo([
                \Magento\Framework\App\Cache\Type\Block::TYPE_IDENTIFIER,
                \Magento\Framework\App\Cache\Type\Collection::TYPE_IDENTIFIER
            ])],
            [$this->equalTo('config')]
        );
        $this->suffixModel->afterSave();
    }
    
    public function testAfterSaveWithoutChanges()
    {
        $this->suffixModel->setValue('');
        $this->suffixModel->setPath(
            \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX
        );
        $this->cacheTypeList->expects($this->never())->method('invalidate');
        $this->suffixModel->afterSave();
    }
    
    public function testAfterSaveProduct()
    {
        $this->suffixModel->setValue('new');
        $this->suffixModel->setPath(
            \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX
        );
        $this->cacheTypeList->expects($this->once())->method('invalidate')->with('config');
        $this->suffixModel->afterSave();
    }
}
