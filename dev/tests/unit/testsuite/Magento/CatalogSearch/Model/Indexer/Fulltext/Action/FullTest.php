<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Action;

class FullTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject */
    protected $resource;
    /** @var \Magento\Catalog\Model\Product\Type|\PHPUnit_Framework_MockObject_MockObject */
    protected $catalogProductType;
    /** @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $eavConfig;
    /** @var \Magento\Framework\Search\Request\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $searchRequestConfig;
    /** @var \Magento\Catalog\Model\Product\Attribute\Source\Status|\PHPUnit_Framework_MockObject_MockObject */
    protected $catalogProductStatus;
    /** @var \Magento\CatalogSearch\Model\Resource\EngineProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $engineProvider;
    /** @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $eventManager;
    /** @var \Magento\CatalogSearch\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $catalogSearchData;
    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfig;
    /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManager;
    /** @var \Magento\Framework\Stdlib\DateTime|\PHPUnit_Framework_MockObject_MockObject */
    protected $dateTime;
    /** @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $localeResolver;
    /** @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $localeDate;
    /** @var \Magento\CatalogSearch\Model\Resource\Fulltext|\PHPUnit_Framework_MockObject_MockObject */
    protected $fulltextResource;
    /** @var \Magento\TestFramework\Helper\ObjectManager */
    protected $objectManagerHelper;
    /** @var \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full */
    protected $object;

    public function setUp()
    {
        $this->resource = $this->getMockBuilder('Magento\\Framework\\App\\Resource')
            ->disableOriginalConstructor()
            ->getMock();
        $this->catalogProductType = $this->getMockBuilder('Magento\\Catalog\\Model\\Product\\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $this->eavConfig = $this->getMockBuilder('Magento\\Eav\\Model\\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchRequestConfig = $this->getMockBuilder('Magento\\Framework\\Search\\Request\\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->catalogProductStatus =
            $this->getMockBuilder('Magento\\Catalog\\Model\\Product\\Attribute\\Source\\Status')
                ->disableOriginalConstructor()
                ->getMock();
        $this->engineProvider = $this->getMockBuilder('Magento\\CatalogSearch\\Model\\Resource\\EngineProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventManager = $this->getMockBuilder('Magento\\Framework\\Event\\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->catalogSearchData = $this->getMockBuilder('Magento\\CatalogSearch\\Helper\\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfig = $this->getMockBuilder('Magento\\Framework\\App\\Config\\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder('Magento\\Framework\\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateTime = $this->getMockBuilder('Magento\\Framework\\Stdlib\\DateTime')
            ->disableOriginalConstructor()
            ->getMock();
        $this->localeResolver = $this->getMockBuilder('Magento\\Framework\\Locale\\ResolverInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->localeDate = $this->getMockBuilder('Magento\\Framework\\Stdlib\\DateTime\\TimezoneInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->fulltextResource = $this->getMockBuilder('Magento\\CatalogSearch\\Model\\Resource\\Fulltext')
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->object = $this->objectManagerHelper->getObject(
            'Magento\\CatalogSearch\\Model\\Indexer\\Fulltext\\Action\\Full',
            array(
                'resource' => $this->resource,
                'catalogProductType' => $this->catalogProductType,
                'eavConfig' => $this->eavConfig,
                'searchRequestConfig' => $this->searchRequestConfig,
                'catalogProductStatus' => $this->catalogProductStatus,
                'engineProvider' => $this->engineProvider,
                'eventManager' => $this->eventManager,
                'catalogSearchData' => $this->catalogSearchData,
                'scopeConfig' => $this->scopeConfig,
                'storeManager' => $this->storeManager,
                'dateTime' => $this->dateTime,
                'localeResolver' => $this->localeResolver,
                'localeDate' => $this->localeDate,
                'fulltextResource' => $this->fulltextResource
            )
        );
    }

    public function testReindexAll()
    {
        $this->storeManager->expects($this->once())->method('getStores')->willReturn([]);
        $this->searchRequestConfig->expects($this->once())->method('reset');
        $this->object->reindexAll();
    }
}
