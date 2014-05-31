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

namespace Magento\Review\Test\TestCase;

use Mtf\Block\Form;
use Magento\Review\Test\Block\Product\View\Summary;
use Magento\Review\Test\Block\Product\View;
use Magento\Review\Test\Fixture\Review;
use Mtf\Factory\Factory;
use Mtf\TestCase\Functional;

/**
 * Product reviews functionality
 *
 */
class ReviewTest extends Functional
{
    /**
     * Adding product review from not logged customer prospective
     *
     * @ZephyrId MAGETWO-12403
     */
    public function testAddReviewByGuest()
    {
        //Preconditions
        $productFixture = Factory::getFixtureFactory()->getMagentoCatalogSimpleProduct();
        $productFixture->switchData('simple_required');
        $productFixture->persist();
        $reviewFixture = Factory::getFixtureFactory()->getMagentoReviewReview();

        //Pages & Blocks
        $homePage = Factory::getPageFactory()->getCmsIndexIndex();
        $productPage = Factory::getPageFactory()->getCatalogProductView();
        $backendReviewPage = Factory::getPageFactory()->getReviewProduct();
        $reviewsSummaryBlock = $productPage->getReviewSummaryBlock();
        $reviewsBlock = $productPage->getCustomerReviewBlock();
        $reviewForm = $productPage->getReviewFormBlock();
        $reviewGrid = $backendReviewPage->getGridBlock();
        $reviewBackendForm = $backendReviewPage->getEditForm();

        //Steps & verifying
        $homePage->open();

        $productPage->init($productFixture);
        $productPage->open();
        $this->verifyNoReviewOnPage($reviewsSummaryBlock);
        $reviewsSummaryBlock->getAddReviewLink()->click();
        $this->assertFalse($reviewsBlock->getFirstReview()->isVisible(), 'No reviews below the form required');

        $reviewForm->fill($reviewFixture);
        $reviewForm->submit();
        $submitReviewMessage = 'Your review has been accepted for moderation.';
        $this->assertContains(
            $submitReviewMessage,
            $productPage->getMessagesBlock()->getSuccessMessages(),
            sprintf('Message "%s" is not appear', $submitReviewMessage)
        );
        $this->verifyNoReviewOnPage($productPage->getReviewSummaryBlock());

        Factory::getApp()->magentoBackendLoginUser();
        $backendReviewPage->open();
        $reviewGrid->searchAndOpen(array('title' => $reviewFixture->getTitle()));
        $this->assertEquals('Guest', $reviewBackendForm->getPostedBy(), 'Review is not posted by Guest');
        $this->assertEquals('Pending', $reviewBackendForm->getStatus(), 'Review is not in Pending status');
        $this->assertTrue(
            $this->verifyReviewBackendForm($reviewFixture, $reviewBackendForm),
            'Review data is not corresponds to submitted one'
        );

        $reviewBackendForm->approveReview();
        $this->assertContains(
            'You saved the review.',
            $backendReviewPage->getMessagesBlock()->getSuccessMessages(),
            'Review is not saved'
        );

        $this->flushCacheStorageWithAssert();

        $productPage->open();
        $reviewsSummaryBlock = $productPage->getReviewSummaryBlock();
        $this->assertTrue($reviewsSummaryBlock->getAddReviewLink()->isVisible(), 'Add review link is not visible');
        $this->assertTrue($reviewsSummaryBlock->getViewReviewLink()->isVisible(), 'View review link is not visible');
        $this->assertContains(
            '1',
            $reviewsSummaryBlock->getViewReviewLink()->getText(),
            'There is more than 1 approved review'
        );

        $reviewForm = $productPage->getReviewFormBlock();
        $reviewsBlock = $productPage->getCustomerReviewBlock();
        $reviewsSummaryBlock->getViewReviewLink()->click();
        $this->assertContains(
            sprintf('You\'re reviewing:%s', $productFixture->getProductName()),
            $reviewForm->getLegend()->getText()
        );
        $this->verifyReview($reviewsBlock, $reviewFixture);
    }

    /**
     * Check that review is no present on the product page
     *
     * @param Summary $summaryBlock
     */
    protected function verifyNoReviewOnPage(Summary $summaryBlock)
    {
        $noReviewLinkText = 'Be the first to review this product';
        $this->assertEquals(
            $noReviewLinkText,
            trim($summaryBlock->getAddReviewLink()->getText()),
            sprintf('"%s" link is not available', $noReviewLinkText)
        );
    }

    /**
     * Flush cache storage and assert success message
     */
    protected function flushCacheStorageWithAssert()
    {
        $cachePage = Factory::getPageFactory()->getAdminCache();
        $cachePage->open();
        $cachePage->getActionsBlock()->flushCacheStorage();
        $this->assertTrue($cachePage->getActionsBlock()->isStorageCacheFlushed(), 'Cache is not flushed');
    }

    /**
     * Verify that submitted review is equals data on page
     *
     * @param View $reviewBlock
     * @param Review $fixture
     */
    protected function verifyReview(View $reviewBlock, Review $fixture)
    {
        $reviewItem = $reviewBlock->getFirstReview();
        foreach ($fixture->getData('fields') as $field => $data) {
            $element = $reviewItem->find($reviewBlock->getFieldSelector($field));
            $this->assertEquals(
                strtolower($data['value']),
                strtolower(trim($element->getText())),
                sprintf('Field "%s" is not equals submitted one.', $field)
            );
        }
    }

    /**
     * Verify that review is equals to data on form
     *
     * @param Review $review
     * @param Form $form
     * @return bool
     */
    protected function verifyReviewBackendForm(Review $review, Form $form)
    {
        $reviewData = [];
        foreach ($review->getData()['fields'] as $key => $field) {
            $reviewData[$key] = $field['value'];
        }
        $dataDiff = array_diff($reviewData, $form->getData($review));

        return empty($dataDiff);
    }
}
