<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdobeIms\Test\Integration\Model;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\AdobeIms\Model\Config;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\AdobeIms\Model\Config.
 */
class ConfigTest extends TestCase
{
    private const SCOPES = ['openid', 'creative_sdk', 'email', 'profile'];
    private const LOCALE = 'en_US';
    private const REDIRECT_URL_PATTERN = '/redirect_uri=[a-zA-Z0-9\/:._]*\/adobe_ims\/oauth\/callback/';

    /**
     * @var Config
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->model = Bootstrap::getObjectManager()->get(Config::class);
    }

    /**
     * Test for getAuthUrl().
     */
    public function testGetAuthUrl(): void
    {
        $result = $this->model->getAuthUrl();

        $this->assertStringContainsString('scope=' . implode(',', self::SCOPES), $result);
        $this->assertStringContainsString('locale=' . self::LOCALE, $result);
        $this->assertMatchesRegularExpression(self::REDIRECT_URL_PATTERN, $result);
    }
}
