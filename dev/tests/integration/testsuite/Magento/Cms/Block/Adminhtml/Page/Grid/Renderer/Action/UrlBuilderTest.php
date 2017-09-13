<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action;

/**
 * Class UrlBuilderTest
 * @package Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action
 */
class UrlBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param string $cookiePath
     * @param string $routePath
     * @param int $scope
     * @param string $store
     *
     * @dataProvider getUrlWithoutSIDDataProvider
     * @see https://jira.corp.magento.com/browse/MAGETWO-69415
     * @security-private
     */
    public function testGetUrlWithoutSID($cookiePath, $routePath, $scope, $store)
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $config = $objectManager->get(\Magento\Framework\Session\Config::class);
        $defaultCookiePath = $config->getCookiePath();
        $config->setCookiePath($cookiePath);

        $urlBuilder = $objectManager->get(\Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder::class);
        $result = $urlBuilder->getUrl($routePath, $scope, $store);
        $config->setCookiePath($defaultCookiePath);

        $parsedUrl = parse_url($result);
        parse_str($parsedUrl['query'], $query);

        $this->assertArrayNotHasKey('SID', $query);
        $this->assertArrayNotHasKey('sid', $query);
    }

    /**
     * @return array
     */
    public function getUrlWithoutSIDDataProvider()
    {
        $cookiePath = '/index.php/admin';
        $routePath = 'home';
        $scope = 0;
        $store = 'admin';

        return [
            [
                $cookiePath,
                $routePath,
                $scope,
                $store,
            ],
        ];
    }
}
