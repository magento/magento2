<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Test\TestCase;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Cms\Test\Fixture\CmsPage;
use Magento\Sitemap\Test\Fixture\Sitemap;
use Magento\Sitemap\Test\Page\Adminhtml\SitemapIndex;
use Magento\Sitemap\Test\Page\Adminhtml\SitemapNew;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Cover generating Sitemap Entity
 *
 * Test Flow:
 * Preconditions:
 *  1. Create category.
 *  2. Create simple product.
 *  3. Create CMS page.
 *  4. Set configurations.
 * Steps:
 *  1. Log in as admin user from data set.
 *  2. Navigate to Marketing > SEO and Search > Site Map.
 *  3. Click "Add Sitemap" button.
 *  4. Fill out all data according to data set.
 *  5. Click "Save" button.
 *  6. Perform all assertions.
 *
 * @group XML_Sitemap
 * @ZephyrId MAGETWO-25124
 */
class GenerateSitemapEntityTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    const SEVERITY = 'S1';
    /* end tags */

    /**
     * Step factory.
     *
     * @var TestStepFactory
     */
    private $stepFactory;

    /**
     * Sitemap grid page
     *
     * @var SitemapIndex
     */
    protected $sitemapIndex;

    /**
     * Sitemap new page
     *
     * @var SitemapNew
     */
    protected $sitemapNew;

    /**
     * Configuration setting.
     *
     * @var string
     */
    private $configData;

    /**
     * Inject data
     *
     * @param SitemapIndex $sitemapIndex
     * @param SitemapNew $sitemapNew
     * @param TestStepFactory $stepFactory
     * @return void
     */
    public function __inject(
        SitemapIndex $sitemapIndex,
        SitemapNew $sitemapNew,
        TestStepFactory $stepFactory
    ) {
        $this->sitemapIndex = $sitemapIndex;
        $this->sitemapNew = $sitemapNew;
        $this->stepFactory = $stepFactory;
    }

    /**
     * Generate Sitemap Entity
     *
     * @param Sitemap $sitemap
     * @param CatalogProductSimple $product
     * @param Category $catalog
     * @param CmsPage $cmsPage
     * @param null|string $configData
     * @return void
     */
    public function testGenerateSitemap(
        Sitemap $sitemap,
        CatalogProductSimple $product,
        Category $catalog,
        CmsPage $cmsPage,
        $configData = null
    ) {
        $this->configData = $configData;

        // Preconditions
        if ($this->configData !== null) {
            $this->stepFactory->create(
                \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
                ['configData' => $this->configData]
            )->run();
        }

        $product->persist();
        $catalog->persist();
        $cmsPage->persist();

        // Steps
        $this->sitemapIndex->open();
        $this->sitemapIndex->getGridPageActions()->addNew();
        $this->sitemapNew->getSitemapForm()->fill($sitemap);
        $this->sitemapNew->getSitemapPageActions()->saveAndGenerate();
    }

    /**
     * Set default configuration.
     *
     * @return void
     */
    public function tearDown()
    {
        if ($this->configData !== null) {
            $this->stepFactory->create(
                \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
                ['configData' => $this->configData, 'rollback' => true]
            )->run();
        }
    }
}
