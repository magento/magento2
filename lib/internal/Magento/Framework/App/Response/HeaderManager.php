<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Response;

use Magento\Framework\App\Response\HeaderProvider\HeaderProviderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Class \Magento\Framework\App\Response\HeaderManager
 *
 */
class HeaderManager
{
    /**
     * @var HeaderProviderInterface[]
     */
    private $headerProviders;

    /**
     * @param HeaderProviderInterface[] $headerProviderList
     * @throws LocalizedException In case one of the header providers is invalid
     */
    public function __construct($headerProviderList)
    {
        foreach ($headerProviderList as $header) {
            if (!($header instanceof HeaderProviderInterface)) {
                throw new LocalizedException(new Phrase('Invalid header provider'));
            }
        }
        $this->headerProviders = $headerProviderList;
    }

    /**
     * @param \Magento\Framework\App\Response\Http $subject
     * @return void
     * @codeCoverageIgnore
     */
    public function beforeSendResponse(\Magento\Framework\App\Response\Http $subject)
    {
        foreach ($this->headerProviders as $provider) {
            if ($provider->canApply()) {
                $subject->setHeader($provider->getName(), $provider->getValue());
            }
        }
    }
}
