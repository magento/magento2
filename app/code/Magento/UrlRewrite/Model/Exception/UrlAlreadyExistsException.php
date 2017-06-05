<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Model\Exception;

use Magento\Framework\Phrase;

/**
 * @api
 */
class UrlAlreadyExistsException extends \Magento\Framework\Exception\LocalizedException
{
    /**
     * @var \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    private $urls = [];

    /**
     * @param Phrase $phrase
     * @param \Exception $cause
     * @param \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    public function __construct(Phrase $phrase = null, \Exception $cause = null, array $urls = [])
    {
        $this->urls = $urls;
        if ($phrase === null) {
            $phrase = new Phrase('URL key for specified store already exists');
        }
        parent::__construct($phrase, $cause);
    }

    /**
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    public function getUrls()
    {
        return $this->urls;
    }
}
