<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CmsUrlRewrite\Test\Unit\Model;

use Magento\CmsUrlRewrite\Model\CmsPageUrlPathGenerator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Filter\FilterManager;
use Magento\Cms\Api\Data\PageInterface;

/**
 * Class \Magento\CmsUrlRewrite\Test\Unit\Model\CmsPageUrlPathGeneratorTest
 */
class CmsPageUrlPathGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|FilterManager
     */
    private $filterManagerMock;

    /**
     * @var CmsPageUrlPathGenerator
     */
    private $model;

    /**
     * Setup environment for test
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManagerHelper($this);
        $this->filterManagerMock = $this->getMockBuilder(FilterManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['translitUrl'])
            ->getMock();

        $this->model = $this->objectManager->getObject(
            CmsPageUrlPathGenerator::class,
            [
                'filterManager' => $this->filterManagerMock
            ]
        );
    }

    /**
     * Test getUrlPath with page has identifier = cms-cookie
     */
    public function testGetUrlPath()
    {
        /* @var PageInterface $cmsPageMock*/
        $cmsPageMock = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $cmsPageMock->expects($this->any())
            ->method('getIdentifier')
            ->willReturn('cms-cookie');

        $this->assertEquals('cms-cookie', $this->model->getUrlPath($cmsPageMock));
    }

    /**
     * Test getCanonicalUrlPath() with page has id = 1
     */
    public function testGetCanonicalUrlPath()
    {
        /* @var PageInterface $cmsPageMock*/
        $cmsPageMock = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $cmsPageMock->expects($this->any())
            ->method('getId')
            ->willReturn('1');

        $this->assertEquals('cms/page/view/page_id/1', $this->model->getCanonicalUrlPath($cmsPageMock));
    }

    /**
     * Test generateUrlKey() with page has no identifier
     */
    public function testGenerateUrlKeyWithNullIdentifier()
    {
        /**
         * Data set
         */
        $page = [
            'identifier' => null,
            'title' => 'CMS Cookie'
        ];

        /* @var PageInterface $cmsPageMock*/
        $cmsPageMock = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $cmsPageMock->expects($this->any())
            ->method('getIdentifier')
            ->willReturn($page['identifier']);

        $cmsPageMock->expects($this->any())
            ->method('getTitle')
            ->willReturn($page['title']);

        $this->filterManagerMock->expects($this->any())
            ->method('translitUrl')
            ->with($page['title'])
            ->willReturn('cms-cookie');

        $this->assertEquals('cms-cookie', $this->model->generateUrlKey($cmsPageMock));
    }

    /**
     * Test generateUrlKey() with page has identifier
     */
    public function testGenerateUrlKeyWithIdentifier()
    {
        /**
         * Data set
         */
        $page = [
            'identifier' => 'home',
            'title' => 'Home Page'
        ];

        /* @var PageInterface $cmsPageMock*/
        $cmsPageMock = $this->getMockBuilder(PageInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $cmsPageMock->expects($this->any())
            ->method('getIdentifier')
            ->willReturn($page['identifier']);

        $cmsPageMock->expects($this->any())
            ->method('getTitle')
            ->willReturn($page['title']);

        $this->filterManagerMock->expects($this->any())
            ->method('translitUrl')
            ->with($page['identifier'])
            ->willReturn('home');

        $this->assertEquals('home', $this->model->generateUrlKey($cmsPageMock));
    }
}
