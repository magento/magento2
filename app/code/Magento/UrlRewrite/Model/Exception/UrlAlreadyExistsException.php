<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * @var array
     */
    private $urls = [];

    /**
     * @param \Magento\Framework\Phrase $phrase
     * @param \Exception $cause
     * @param int $code
     * @param array $urls
     */
    public function __construct(Phrase $phrase = null, \Exception $cause = null, $code = 0, array $urls = [])
    {
        $this->urls = $urls;
        if ($phrase === null) {
            $phrase = __('URL key for specified store already exists');
        }
        parent::__construct($phrase, $cause, $code);
    }

    /**
     * @return array
     */
    public function getUrls()
    {
        return $this->urls;
    }
}
