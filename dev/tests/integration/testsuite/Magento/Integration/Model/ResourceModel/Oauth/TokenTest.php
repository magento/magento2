<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Model\ResourceModel\Oauth;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Integration\Model\Oauth\Token;

/**
 * Integration test for @see \Magento\Integration\Model\ResourceModel\Oauth\Token
 *
 * Also tests @see \Magento\Integration\Cron\CleanExpiredTokens
 */
class TokenTest extends \PHPUnit_Framework_TestCase
{
    const TOKEN_LIFETIME = 1; // in hours
    
    const BASE_CREATED_AT_TIMESTAMP = 100000;
    
    /**
     * @var array
     */
    private $generatedTokens;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime | \PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeMock;

    /**
     * @var \Magento\Integration\Model\ResourceModel\Oauth\Token
     */
    private $tokenResourceModel;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Integration\Model\Oauth\TokenFactory
     */
    private $tokenFactory;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->tokenFactory = $this->objectManager->create(\Magento\Integration\Model\Oauth\TokenFactory::class);

        /** Mock date model to be able to specify "current timestamp" and avoid dependency on real timestamp */
        $this->dateTimeMock = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \Magento\Integration\Model\ResourceModel\Oauth\Token $tokenResourceModel */
        $this->tokenResourceModel = $this->objectManager->create(
            \Magento\Integration\Model\ResourceModel\Oauth\Token::class,
            ['date' => $this->dateTimeMock]
        );

        $this->generatedTokens = $this->generateTokens();

        parent::setUp();
    }

    /**
     * @return array
     */
    private function generateTokens()
    {
        /** Generate several tokens with different user types and created at combinations */
        $tokensToBeGenerated = [
            '#1' => [
                'userType' => UserContextInterface::USER_TYPE_ADMIN,
                'createdAt' => self::BASE_CREATED_AT_TIMESTAMP
            ],
            '#2' => [
                'userType' => UserContextInterface::USER_TYPE_ADMIN,
                'createdAt' => self::BASE_CREATED_AT_TIMESTAMP + 5
            ],
            '#3' => [
                'userType' => UserContextInterface::USER_TYPE_CUSTOMER,
                'createdAt' => self::BASE_CREATED_AT_TIMESTAMP
            ],
            '#4' => [
                'userType' => UserContextInterface::USER_TYPE_CUSTOMER,
                'createdAt' => self::BASE_CREATED_AT_TIMESTAMP - 5
            ],
            '#5' => [
                'userType' => UserContextInterface::USER_TYPE_INTEGRATION,
                'createdAt' => self::BASE_CREATED_AT_TIMESTAMP
            ],
            '#6' => [
                'userType' => UserContextInterface::USER_TYPE_INTEGRATION,
                'createdAt' => self::BASE_CREATED_AT_TIMESTAMP + 5
            ],
        ];
        /** @var \Magento\Framework\Stdlib\DateTime $dateTimeUtils */
        $dateTimeUtils = $this->objectManager->get(\Magento\Framework\Stdlib\DateTime::class);
        foreach ($tokensToBeGenerated as &$tokenData) {
            $token = $this->tokenFactory->create();
            $token->setType(Token::TYPE_ACCESS)
                ->setUserType($tokenData['userType'])
                ->setToken(rand(1, PHP_INT_MAX))
                ->setCreatedAt($dateTimeUtils->formatDate($tokenData['createdAt']));
            $this->tokenResourceModel->save($token);
            $tokenData['tokenId'] = $token->getId();
        }
        return $tokensToBeGenerated;
    }

    /**
     * Make sure that @see \Magento\Integration\Cron\CleanExpiredTokens cleans tokens correctly per configuration
     *
     * 1. Generate several tokens with different user type and creation time
     * 2. Emulate current time stamp to be equal to (expiration period + some adjustment)
     * 3. Run clean up
     * 4. Make sure that clean up removed tokens that were expected to be removed,
     *    and those tokens which were not expected to be removed are still there
     *
     * @param int $secondsAfterBaseCreatedTimestamp
     * @param array $expectedRemovedTokenNumbers
     * @param array $expectedPreservedTokenNumbers
     *
     * @dataProvider deleteExpiredTokenUsingObserverDataProvider
     * @covers \Magento\Integration\Cron\CleanExpiredTokens::execute
     */
    public function testDeleteExpiredTokenUsingObserver(
        $secondsAfterBaseCreatedTimestamp,
        $expectedRemovedTokenNumbers,
        $expectedPreservedTokenNumbers
    ) {
        /** @var \Magento\Integration\Cron\CleanExpiredTokens $cleanExpiredTokensModel */
        $cleanExpiredTokensModel = $this->objectManager->create(
            \Magento\Integration\Cron\CleanExpiredTokens::class,
            ['tokenResourceModel' => $this->tokenResourceModel]
        );

        $emulatedCurrentTimestamp = self::BASE_CREATED_AT_TIMESTAMP + $secondsAfterBaseCreatedTimestamp;
        $this->dateTimeMock->method('gmtTimestamp')->willReturn($emulatedCurrentTimestamp);
        $cleanExpiredTokensModel->execute();
        $this->assertTokenCleanUp(
            $expectedRemovedTokenNumbers,
            $expectedPreservedTokenNumbers,
            $this->generatedTokens
        );
    }

    public function deleteExpiredTokenUsingObserverDataProvider()
    {
        return [
            "Clean up long before default admin and default customer token life time" => [
                3600 - 6, // time passed after base creation time
                [], // expected to be removed
                ['#1', '#2', '#3', '#4', '#5', '#6'], // expected to exist
            ],
            "Clean up just before default admin and default customer token life time" => [
                3600 - 1, // time passed after base creation time
                ['#4'], // expected to be removed
                ['#1', '#2', '#3', '#5', '#6'], // expected to exist
            ],
            "Clean up after default admin token life time, but before default customer token life time" => [
                3600 + 1, // time passed after base creation time
                ['#3', '#4'], // expected to be removed
                ['#1', '#2', '#5', '#6'], // expected to exist
            ],
            "Clean up after default customer and default admin token life time" => [
                14400 + 1, // time passed after base creation time
                ['#1', '#3', '#4'], // expected to be removed
                ['#2', '#5', '#6'], // expected to exist
            ],
        ];
    }

    /**
     * Verify that expired tokens removal works as expected, @see \Magento\Integration\Model\ResourceModel\Oauth\Token
     *
     * 1. Generate several tokens with different user type and creation time
     * 2. Emulate current time stamp to be equal to (expiration period + some adjustment)
     * 3. Run clean up for some token types
     * 4. Make sure that clean up removed tokens that were expected to be removed,
     *    and those tokens which were not expected to be removed are still there
     *
     * @param $secondsAfterBaseCreatedTimestamp
     * @param $tokenTypesToClean
     * @param $expectedRemovedTokenNumbers
     * @param $expectedPreservedTokenNumbers
     *
     * @magentoDbIsolation enabled
     * @dataProvider deleteExpiredTokensDataProvider
     * @covers \Magento\Integration\Model\ResourceModel\Oauth\Token::deleteExpiredTokens
     */
    public function testDeleteExpiredTokens(
        $secondsAfterBaseCreatedTimestamp,
        $tokenTypesToClean,
        $expectedRemovedTokenNumbers,
        $expectedPreservedTokenNumbers
    ) {
        /** Run clean up for tokens of {$tokenTypesToClean} type, created {$secondsAfterBaseCreatedTimestamp} ago */
        $emulatedCurrentTimestamp = self::BASE_CREATED_AT_TIMESTAMP + $secondsAfterBaseCreatedTimestamp;
        $this->dateTimeMock->method('gmtTimestamp')->willReturn($emulatedCurrentTimestamp);
        $this->tokenResourceModel->deleteExpiredTokens(self::TOKEN_LIFETIME, $tokenTypesToClean);
        $this->assertTokenCleanUp(
            $expectedRemovedTokenNumbers,
            $expectedPreservedTokenNumbers,
            $this->generatedTokens
        );
    }

    public function deleteExpiredTokensDataProvider()
    {
        return [
          "Clean up for admin tokens which were created ('token_lifetime' + 1 second) ago" => [
              self::TOKEN_LIFETIME * 60 * 60 + 1, // time passed after base creation time
              [UserContextInterface::USER_TYPE_ADMIN], // token types to clean up
              ['#1'], // expected to be removed
              ['#2', '#3', '#4', '#5', '#6'], // expected to exist
          ],
          "Clean up for admin, integration, guest tokens which were created ('token_lifetime' + 6 second) ago" => [
              self::TOKEN_LIFETIME * 60 * 60 + 6, // time passed after base creation time
              [ // token types to clean up
                  UserContextInterface::USER_TYPE_ADMIN,
                  UserContextInterface::USER_TYPE_INTEGRATION,
                  UserContextInterface::USER_TYPE_GUEST
              ],
              ['#1', '#2', '#5', '#6'], // expected to be removed
              ['#3', '#4'], // expected to exist
          ],
          "Clean up for admin, integration, customer tokens which were created ('token_lifetime' + 6 second) ago" => [
              self::TOKEN_LIFETIME * 60 * 60 + 6, // time passed after base creation time
              [ // token types to clean up
                  UserContextInterface::USER_TYPE_ADMIN,
                  UserContextInterface::USER_TYPE_INTEGRATION,
                  UserContextInterface::USER_TYPE_CUSTOMER
              ],
              ['#1', '#2', '#3', '#4', '#5', '#6'], // expected to be removed
              [], // expected to exist
          ],
          "Clean up for admin, integration, customer tokens which were created ('token_lifetime' + 1 second) ago" => [
              self::TOKEN_LIFETIME * 60 * 60 + 1, // time passed after base creation time
              [ // token types to clean up
                  UserContextInterface::USER_TYPE_ADMIN,
                  UserContextInterface::USER_TYPE_INTEGRATION,
                  UserContextInterface::USER_TYPE_CUSTOMER
              ],
              ['#1', '#3', '#4', '#5'], // expected to be removed
              ['#2', '#6'], // expected to exist
          ],
        ];
    }

    /**
     * Make that only exired tokens were cleaned up
     *
     * @param array $expectedRemovedTokenNumbers
     * @param array $expectedPreservedTokenNumbers
     * @param array $generatedTokens
     */
    private function assertTokenCleanUp(
        $expectedRemovedTokenNumbers,
        $expectedPreservedTokenNumbers,
        $generatedTokens
    ) {
        foreach ($expectedRemovedTokenNumbers as $tokenNumber) {
            $token = $this->tokenFactory->create();
            $this->tokenResourceModel->load($token, $generatedTokens[$tokenNumber]['tokenId']);
            $this->assertEmpty(
                $token->getId(),
                "Token {$tokenNumber} was expected to be deleted after clean up"
            );
        }
        foreach ($expectedPreservedTokenNumbers as $tokenNumber) {
            $token = $this->tokenFactory->create();
            $this->tokenResourceModel->load($token, $generatedTokens[$tokenNumber]['tokenId']);
            $this->assertEquals(
                $generatedTokens[$tokenNumber]['tokenId'],
                $token->getId(),
                "Token {$tokenNumber} was NOT expected to be deleted after clean up"
            );
        }
    }
}
