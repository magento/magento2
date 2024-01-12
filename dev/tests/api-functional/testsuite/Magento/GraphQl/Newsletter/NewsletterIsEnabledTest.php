<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Newsletter;

use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test newsletter enabled query
 */
class NewsletterIsEnabledTest extends GraphQlAbstract
{
    private const QUERY =  <<<QRY
{
    storeConfig {
       newsletter_enabled
    }
}
QRY;

    #[
        Config('newsletter/general/active', 1),
    ]
    public function testNewsletterIsEnabled()
    {
        $this->assertEquals(
            [
                'storeConfig' => [
                    'newsletter_enabled' => true
                ]
            ],
            $this->graphQlQuery(
                self::QUERY
            )
        );
    }

    #[
        Config('newsletter/general/active', 0),
    ]
    public function testNewsletterIsDisabled()
    {
        $this->assertEquals(
            [
                'storeConfig' => [
                    'newsletter_enabled' => false
                ]
            ],
            $this->graphQlQuery(
                self::QUERY
            )
        );
    }
}
