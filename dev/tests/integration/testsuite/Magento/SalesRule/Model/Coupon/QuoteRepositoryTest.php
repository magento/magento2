<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SalesRule\Model\Coupon;

use Magento\Captcha\Model\DefaultModel;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\SalesRule\Api\Exception\CodeRequestLimitException;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Captcha\Helper\Data as CaptchaHelper;
use Magento\TestFramework\ObjectManager;

/**
 * Test cases related to coupons.
 */
class QuoteRepositoryTest extends TestCase
{
    /**
     * @var CartRepositoryInterface
     */
    private $repo;

    /**
     * @var SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @var Http
     */
    private $request;

    /**
     * @var CaptchaHelper
     */
    private $captchaHelper;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        /** @var ObjectManager $objectManager */
        $objectManager = Bootstrap::getObjectManager();
        /** @var Http $request */
        $request = $objectManager->get(RequestInterface::class);
        $request->getServer()->set('REMOTE_ADDR', '127.0.0.1');
        $this->request = $request;
        $objectManager->removeSharedInstance(RemoteAddress::class);
        $this->repo = $objectManager->get(CartRepositoryInterface::class);
        $this->criteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $this->captchaHelper = $objectManager->get(CaptchaHelper::class);
    }

    /**
     * Load cart from fixture.
     *
     * @return CartInterface
     */
    private function getCart(): CartInterface
    {
        $carts = $this->repo->getList(
            $this->criteriaBuilder->addFilter('reserved_order_id', 'test01')->create()
        )->getItems();
        if (!$carts) {
            throw new \RuntimeException('Cart from fixture not found');
        }

        return array_shift($carts);
    }

    /**
     * Case when coupon requests limit is reached.
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     *
     * @magentoConfigFixture default_store customer/captcha/enable 1
     * @magentoConfigFixture default_store customer/captcha/forms sales_rule_coupon_request
     * @magentoConfigFixture default_store customer/captcha/failed_attempts_login 10
     * @magentoConfigFixture default_store customer/captcha/failed_attempts_ip 2
     *
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDataFixture Magento/SalesRule/_files/coupon_cart_fixed_discount.php
     *
     */
    public function testAboveLimitFail()
    {
        $this->expectException(\Magento\SalesRule\Api\Exception\CodeRequestLimitException::class);
        //Making number of requests above limit.
        try {
            $this->repo->save($this->getCart()->setCouponCode('fake20'));
            $this->repo->save($this->getCart()->setCouponCode('fake21'));
        } catch (CodeRequestLimitException $exception) {
            $this->fail('Denied access before the limit is reached.');
        }
        $this->repo->save($this->getCart()->setCouponCode('fake22'));
    }

    /**
     * Case when coupon requests limit reached but genuine request provided.
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     *
     * @magentoConfigFixture default_store customer/captcha/enable 1
     * @magentoConfigFixture default_store customer/captcha/forms sales_rule_coupon_request
     * @magentoConfigFixture default_store customer/captcha/failed_attempts_login 10
     * @magentoConfigFixture default_store customer/captcha/failed_attempts_ip 2
     *
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDataFixture Magento/SalesRule/_files/coupon_cart_fixed_discount.php
     */
    public function testAboveLimitSuccess()
    {
        $this->repo->save($this->getCart()->setCouponCode('fake24'));
        $this->repo->save($this->getCart()->setCouponCode('fake25'));

        //Providing genuine proof.
        /** @var DefaultModel $captcha */
        $captcha = $this->captchaHelper->getCaptcha('sales_rule_coupon_request');
        $captcha->generate();
        $this->request->setPostValue('captcha', ['sales_rule_coupon_request' => $captcha->getWord()]);
        $this->repo->save($this->getCart()->setCouponCode('fake26'));
    }
}
