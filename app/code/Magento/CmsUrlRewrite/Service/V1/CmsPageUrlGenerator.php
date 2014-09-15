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
namespace Magento\CmsUrlRewrite\Service\V1;

use Magento\Framework\StoreManagerInterface;
use Magento\UrlRedirect\Service\V1\Data\Converter;
use Magento\UrlRedirect\Service\V1\Data\UrlRewrite;

class CmsPageUrlGenerator implements CmsPageUrlGeneratorInterface
{
    /**
     * Entity type code
     */
    const ENTITY_TYPE = 'cms-page';

    /**
     * @var Converter
     */
    protected $converter;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $cmsPage;

    /**
     * @param Converter $converter
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Converter $converter,
        StoreManagerInterface $storeManager
    ) {
        $this->converter = $converter;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($cmsPage)
    {
        $stores = $cmsPage->getStores();
        $this->cmsPage = $cmsPage;
        $urls = array_search('0', $stores) === false ? $this->generateForSpecificStores($stores)
            : $this->generateForAllStores();
        $this->cmsPage = null;
        return $urls;
    }

    /**
     * Generate list of urls for default store
     *
     * @return UrlRewrite[]
     */
    protected function generateForAllStores()
    {
        $urls = [];
        foreach ($this->storeManager->getStores() as $store) {
            $urls[] = $this->createUrlRewrite($store->getStoreId());
        }
        return $urls;
    }

    /**
     * Generate list of urls per store
     *
     * @param int[] $storeIds
     * @return UrlRewrite[]
     */
    protected function generateForSpecificStores($storeIds)
    {
        $urls = [];
        $existingStores = $this->storeManager->getStores();
        foreach ($storeIds as $storeId) {
            if (!isset($existingStores[$storeId])) {
                continue;
            }
            $urls[] = $this->createUrlRewrite($storeId);
        }
        return $urls;
    }

    /**
     * Create url rewrite object
     *
     * @param int $storeId
     * @param string|null $redirectType Null or one of OptionProvider const
     * @return UrlRewrite
     */
    protected function createUrlRewrite($storeId, $redirectType = null)
    {
        return $this->converter->convertArrayToObject([
            UrlRewrite::ENTITY_TYPE => self::ENTITY_TYPE,
            UrlRewrite::ENTITY_ID => $this->cmsPage->getId(),
            UrlRewrite::STORE_ID => $storeId,
            UrlRewrite::REQUEST_PATH => $this->cmsPage->getIdentifier(),
            UrlRewrite::TARGET_PATH => 'cms/page/view/page_id/' . $this->cmsPage->getId(),
            UrlRewrite::REDIRECT_TYPE => $redirectType,
        ]);
    }
}
