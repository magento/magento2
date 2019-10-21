<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\StoreGraphQl\Test\Integration;

use Magento\Framework\App\Request\Http;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Registry;
use Magento\Store\Model\Store;
use Magento\StoreGraphQl\Controller\HttpRequestValidator\StoreValidator;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Zend\Http\Headers;

class StoreValidatorTest extends TestCase
{
    /**
     * @var Store
     */
    private $disabledStore;

    /**
     * @var StoreValidator
     */
    private $storeValidator;

    /**
     * @var Http
     */
    private $httpRequest;

    public function __construct()
    {
        parent::__construct();
        $this->storeValidator = Bootstrap::getObjectManager()->create(StoreValidator::class);
        $this->httpRequest = Bootstrap::getObjectManager()->create(Http::class);
    }

    protected function setUp()
    {
        Bootstrap::getObjectManager()->get(Registry::class)->register('isSecureArea', true);

        $this->disabledStore = Bootstrap::getObjectManager()->create(Store::class)
            ->setCode('inactive_store')
            ->setWebsiteId(1)
            ->setGroupId(1)
            ->setName('Inactive Store')
            ->setIsActive(false)
            ->save();
    }

    protected function tearDown()
    {
        $this->disabledStore->delete();
        Bootstrap::getObjectManager()->get(Registry::class)->unregister('isSecureArea');
    }

    public function testExceptionIsThrownWhenStoreIsNotValid()
    {
        $this->expectException(GraphQlInputException::class);
        $this->httpRequest->setHeaders(Headers::fromString('Store: inactive_store'));
        $this->storeValidator->validate($this->httpRequest);
    }

    public function testExceptionIsThrownWhenStoreDoesNotExist()
    {
        $this->expectException(GraphQlInputException::class);
        $this->httpRequest->setHeaders(Headers::fromString('Store: nonexistent_store'));
        $this->storeValidator->validate($this->httpRequest);
    }
}
