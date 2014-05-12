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
namespace Magento\Cms\Helper;

/**
 * @magentoAppArea frontend
 */
class PageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Cms/_files/pages.php
     */
    public function testRenderPage()
    {

        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $httpContext = $objectManager->get('Magento\Framework\App\Http\Context');
        $httpContext->setValue(\Magento\Customer\Helper\Data::CONTEXT_AUTH, false, false);
        $objectManager->get('Magento\Framework\App\State')->setAreaCode('frontend');
        $arguments = array(
            'request' => $objectManager->get('Magento\TestFramework\Request'),
            'response' => $objectManager->get('Magento\TestFramework\Response')
        );
        $context = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\App\Action\Context',
            $arguments
        );
        $page = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Cms\Model\Page');
        $page->load('page_design_blank', 'identifier');
        // fixture
        /** @var $pageHelper \Magento\Cms\Helper\Page */
        $pageHelper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Cms\Helper\Page');
        $result = $pageHelper->renderPage(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                'Magento\Framework\App\Action\Action',
                array('context' => $context)
            ),
            $page->getId()
        );
        $design = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\DesignInterface'
        );
        $this->assertEquals('Magento/blank', $design->getDesignTheme()->getThemePath());
        $this->assertTrue($result);
    }
}
