<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\App\State;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\App\Http as HttpApp;
use Magento\Framework\App\ObjectManager as AppObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\ApplicationStateComparator\Comparator;
use Magento\Framework\TestFramework\ApplicationStateComparator\ObjectManager;
use Magento\GraphQl\Quote\GetMaskedQuoteIdByReservedOrderId;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests the dispatch method in the GraphQl Controller class using a simple product query
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GraphQlStateDiff
{
    private const CONTENT_TYPE = 'application/json';

    /**
     * @var ObjectManagerInterface
     *
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly ObjectManagerInterface $objectManagerBeforeTest;

    /**
     * @var ObjectManager
     *
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly ObjectManager $objectManagerForTest;

    /**
     * @var Comparator
     *
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly Comparator $comparator;

    /**
     * Constructor
     *
     * @param TestCase $test
     */
    public function __construct(TestCase $test)
    {
        if (8 == PHP_MAJOR_VERSION && 3 == PHP_MINOR_VERSION && PHP_RELEASE_VERSION  < 5) {
            $test->markTestSkipped(
                "This test isn't compatible with PHP 8.3 versions less than PHP 8.3.5 because of "
                . "bug in garbage collector. https://github.com/php/php-src/issues/13569"
                . " will roll back in AC-11491"
            );
        }
        $this->objectManagerBeforeTest = Bootstrap::getObjectManager();
        $this->objectManagerForTest = new ObjectManager($this->objectManagerBeforeTest);
        $this->objectManagerForTest->getFactory()->setObjectManager($this->objectManagerForTest);
        AppObjectManager::setInstance($this->objectManagerForTest);
        Bootstrap::setObjectManager($this->objectManagerForTest);
        $this->comparator = $this->objectManagerForTest->create(Comparator::class);
        $this->objectManagerForTest->_resetState();
    }

    /**
     * Gets test object manager
     *
     * @return ObjectManager
     */
    public function getTestObjectManager()
    {
        return $this->objectManagerForTest;
    }

    /**
     * Tear Down
     *
     * @return void
     */
    public function tearDown(): void
    {
        $this->objectManagerBeforeTest->getFactory()->setObjectManager($this->objectManagerBeforeTest);
        AppObjectManager::setInstance($this->objectManagerBeforeTest);
        Bootstrap::setObjectManager($this->objectManagerBeforeTest);
    }

    /**
     * Tests state
     *
     * @param string $query
     * @param array $variables
     * @param array $variables2
     * @param array $authInfo
     * @param string $operationName
     * @param string $expected
     * @param TestCase $test
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\AuthenticationException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function testState(
        string $query,
        array $variables,
        array $variables2,
        array $authInfo,
        string $operationName,
        string $expected,
        TestCase $test
    ): void {
        if (array_key_exists(1, $authInfo)) {
            $authInfo1 = $authInfo[0];
            $authInfo2 = $authInfo[1];
        } else {
            $authInfo1 = $authInfo;
            $authInfo2 = $authInfo;
        }
        $jsonEncodedRequest = json_encode([
            'query' => $query,
            'variables' => $variables,
            'operationName' => $operationName
        ]);
        $output1 = $this->request($jsonEncodedRequest, $operationName, $authInfo1, $test, true);
        $test->assertStringContainsString($expected, $output1);
        if ($operationName === 'placeOrder' || $operationName === 'mergeCarts') {
            foreach ($variables as $cartId) {
                $this->reactivateCart($cartId);
            }
        } elseif ($operationName==='applyCouponToCart') {
            $this->removeCouponFromCart($variables);
        } elseif ($operationName==='resetPassword') {
            $variables2['resetPasswordToken'] = $this->getResetPasswordToken($variables['email']);
            $variables2['email'] = $variables['email'];
            $variables2['newPassword'] = $variables['newPassword'];
        }

        if ($variables2) {
            $jsonEncodedRequest = json_encode([
                'query' => $query,
                'variables' => $variables2,
                'operationName' => $operationName
            ]);
        }
        $output2 = $this->request($jsonEncodedRequest, $operationName, $authInfo2, $test);
        $test->assertStringContainsString($expected, $output2);
    }

    /**
     * Makes request
     *
     * @param string $query
     * @param string $operationName
     * @param array $authInfo
     * @param TestCase $test
     * @param bool $firstRequest
     * @return string
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    private function request(
        string $query,
        string $operationName,
        array $authInfo,
        TestCase $test,
        bool $firstRequest = false
    ): string {
        $this->objectManagerForTest->_resetState();
        $this->comparator->rememberObjectsStateBefore($firstRequest);
        $response = $this->doRequest($query, $authInfo);
        $this->objectManagerForTest->_resetState();
        $this->comparator->rememberObjectsStateAfter($firstRequest);
        $result = $this->comparator->compareBetweenRequests($operationName);
        $test->assertEmpty(
            $result,
            sprintf(
                '%d objects changed state during request. Details: %s',
                count($result),
                var_export($result, true)
            )
        );
        $result = $this->comparator->compareConstructedAgainstCurrent($operationName);
        $test->assertEmpty(
            $result,
            sprintf(
                '%d objects changed state since constructed. Details: %s',
                count($result),
                var_export($result, true)
            )
        );
        return $response;
    }

    /**
     * Process the GraphQL request
     *
     * @param string $query
     * @param array $authInfo
     * @return mixed|string
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    private function doRequest(string $query, array $authInfo)
    {
        $request = $this->objectManagerForTest->get(RequestInterface::class);
        $request->setContent($query);
        $request->setMethod('POST');
        $request->setPathInfo('/graphql');
        $request->getHeaders()->addHeaders(['content_type' => self::CONTENT_TYPE]);
        if ($authInfo) {
            $email = $authInfo['email'];
            $password = $authInfo['password'];
            $customerToken = $this->objectManagerForTest->get(CustomerTokenServiceInterface::class)
                ->createCustomerAccessToken($email, $password);
            $request->getHeaders()->addHeaders(['Authorization' => 'Bearer ' . $customerToken]);
        }
        $unusedResponse = $this->objectManagerForTest->create(HttpResponse::class);
        $httpApp = $this->objectManagerForTest->create(
            HttpApp::class,
            ['request' => $request, 'response' => $unusedResponse]
        );
        $actualResponse = $httpApp->launch();
        return $actualResponse->getContent();
    }

    /**
     * Removes coupon from cart
     *
     * @param array $variables
     * @return void
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    private function removeCouponFromCart(array $variables)
    {
        $couponManagement = $this->objectManagerForTest->get(\Magento\Quote\Api\CouponManagementInterface::class);
        $cartId = $this->getCartId($variables['cartId']);
        $couponManagement->remove($cartId);
    }

    /**
     * Reactivates cart
     *
     * @param string $cartId
     * @return void
     * @throws NoSuchEntityException
     */
    private function reactivateCart(string $cartId)
    {
        $cartId = $this->getCartId($cartId);
        $cart = $this->objectManagerForTest->get(\Magento\Quote\Model\Quote::class);
        $cart->load($cartId);
        $cart->setIsActive(true);
        $cart->save();
    }

    /**
     * Gets cart id
     *
     * @param string $cartId
     * @return int
     * @throws NoSuchEntityException
     */
    private function getCartId(string $cartId)
    {
        $maskedQuoteIdToQuoteId = $this->objectManagerForTest->get(MaskedQuoteIdToQuoteIdInterface::class);
        return $maskedQuoteIdToQuoteId->execute($cartId);
    }

    /**
     * Gets cart id hash
     *
     * @param string $cartId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCartIdHash(string $cartId): string
    {
        $getMaskedQuoteIdByReservedOrderId = $this->getTestObjectManager()
            ->get(GetMaskedQuoteIdByReservedOrderId::class);
        return $getMaskedQuoteIdByReservedOrderId->execute($cartId);
    }

    /**
     * Get reset password token
     *
     * @param string $email
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getResetPasswordToken(string $email): string
    {
        $accountManagement = $this->objectManagerForTest->get(AccountManagementInterface::class);
        $customerRegistry = $this->objectManagerForTest->get(CustomerRegistry::class);
        $accountManagement->initiatePasswordReset(
            $email,
            AccountManagement::EMAIL_RESET,
            1
        );

        $customerSecure = $customerRegistry->retrieveSecureData(1);
        return $customerSecure->getRpToken();
    }
}
