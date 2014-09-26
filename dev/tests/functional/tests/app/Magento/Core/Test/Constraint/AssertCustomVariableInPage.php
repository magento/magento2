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

namespace Magento\Core\Test\Constraint;

use Mtf\Client\Browser;
use Mtf\Fixture\FixtureFactory;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Store\Test\Fixture\Store;
use Mtf\Constraint\AbstractConstraint;
use Magento\Core\Test\Fixture\SystemVariable;

/**
 * Class AssertCustomVariableInPage
 */
class AssertCustomVariableInPage extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Add created variable to page and assert that Custom Variable is displayed on frontend page and has
     * correct data according to dataset.
     *
     * @param SystemVariable $customVariable
     * @param CmsIndex $cmsIndex
     * @param SystemVariable $variable
     * @param FixtureFactory $fixtureFactory
     * @param Browser $browser
     * @param Store $storeOrigin
     * @param SystemVariable $customVariableOrigin
     * @return void
     */
    public function processAssert(
        SystemVariable $customVariable,
        CmsIndex $cmsIndex,
        SystemVariable $variable,
        FixtureFactory $fixtureFactory,
        Browser $browser,
        Store $storeOrigin = null,
        SystemVariable $customVariableOrigin = null
    ) {
        $cmsPage = $fixtureFactory->createByCode(
            'cmsPage',
            [
                'dataSet' => 'default',
                'data' => [
                    'content' => [
                        'content' => '{{customVar code=' . $customVariable->getCode() . '}}'
                    ]
                ],
            ]
        );
        $cmsPage->persist();
        $browser->open($_ENV['app_frontend_url'] . $cmsPage->getIdentifier());

        $cmsIndex->getStoreSwitcherBlock()->selectStoreView('Default Store View');

        $htmlValue = $customVariableOrigin
            ? $this->getHtmlValue($customVariable, $customVariableOrigin)
            : strip_tags($customVariable->getHtmlValue());
        $pageContent = $cmsIndex->getCmsPageBlock()->getPageContent();
        $this->checkVariable($htmlValue, $pageContent);

        if ($storeOrigin !== null) {
            $cmsIndex->getStoreSwitcherBlock()->selectStoreView($storeOrigin->getName());
            $htmlValue = strip_tags($customVariable->getHtmlValue());
            if ($htmlValue === '') {
                $htmlValue = strip_tags($variable->getHtmlValue());
            }
            $pageContent = $cmsIndex->getCmsPageBlock()->getPageContent();
            $this->checkVariable($htmlValue, $pageContent);
        }
    }

    /**
     * Get html value
     *
     * @param SystemVariable $customVariable
     * @param SystemVariable $customVariableOrigin
     * @return string
     */
    protected function getHtmlValue(SystemVariable $customVariable, SystemVariable $customVariableOrigin)
    {
        $data = array_merge($customVariableOrigin->getData(), $customVariable->getData());
        if ($customVariable->getHtmlValue() == "" && $customVariableOrigin->getHtmlValue() == "") {
            $htmlValue = ($data['plain_value'] == "")
                ? $customVariableOrigin->getPlainValue()
                : $data['plain_value'];
        } else {
            $htmlValue = ($customVariableOrigin == null)
                ? $customVariable->getHtmlValue()
                : $customVariableOrigin->getHtmlValue();
            $htmlValue = strip_tags($htmlValue);
        }
        return $htmlValue;
    }

    /**
     * Check Variable on frontend page
     *
     * @param string $htmlValue
     * @param string $pageContent
     * @return void
     */
    protected function checkVariable($htmlValue, $pageContent)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            $htmlValue,
            $pageContent,
            'Wrong content is displayed on frontend page'
            . "\nExpected: " . $htmlValue
            . "\nActual: " . $pageContent
        );
    }

    /**
     * Returns a string representation of successful assertion
     *
     * @return string
     */
    public function toString()
    {
        return 'Custom Variable is displayed on frontend page';
    }
}
