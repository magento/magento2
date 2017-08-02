<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Model\Exception;

use Magento\Framework\Phrase;

/**
 * @api
 * @since 2.2.0
 */
class UrlAlreadyExistsException extends \Magento\Framework\Exception\LocalizedException
{
    /**
     * @var array
     * @since 2.2.0
     */
    private $urls = [];

    /**
     * @param \Magento\Framework\Phrase $phrase
     * @param \Exception $cause
     * @param int $code
     * @param array $urls
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function getUrls()
    {
        return $this->urls;
    }
}
