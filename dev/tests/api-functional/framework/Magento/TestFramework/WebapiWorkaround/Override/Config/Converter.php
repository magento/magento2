<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\WebapiWorkaround\Override\Config;

use Magento\TestFramework\Annotation\AdminConfigFixture;
use Magento\TestFramework\Annotation\ApiDataFixture;
use Magento\TestFramework\Annotation\ConfigFixture;
use Magento\TestFramework\Annotation\DataFixture;
use Magento\TestFramework\Annotation\DataFixtureBeforeTransaction;
use Magento\TestFramework\Workaround\Override\Config\Converter as IntegrationConverter;

/**
 * Converter for api tests config
 */
class Converter extends IntegrationConverter
{
    /**
     * Fill node attributes values
     *
     * @param \DOMElement $fixture
     * @return array
     */
    protected function fillAttributes(\DOMElement $fixture): array
    {
        $result = [];
        switch ($fixture->nodeName) {
            case DataFixtureBeforeTransaction::ANNOTATION:
            case DataFixture::ANNOTATION:
            case ApiDataFixture::ANNOTATION:
                $result = $this->fillDataFixtureAttributes($fixture);
                break;
            case ConfigFixture::ANNOTATION:
                $result = $this->fillConfigFixtureAttributes($fixture);
                break;
            case AdminConfigFixture::ANNOTATION:
                $result = $this->fillAdminConfigFixtureAttributes($fixture);
                break;
            default:
                break;
        }

        return $result;
    }
}
