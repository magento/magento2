<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Usps\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Validator\Url;

/**
 * Represents a config URL that may point to a USPS endpoint
 *
 * @SuppressWarnings(PHPMD.Superglobals)
 */
class UspsUrl extends Value
{
    /**
     * @var Url
     */
    private Url $url;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param Url $url
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        Url $url,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->url = $url;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @inheritdoc
     *
     * @throws ValidatorException
     */
    public function beforeSave()
    {
        $isValid = $this->url->isValid($this->getValue());
        if ($isValid) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $host = parse_url((string)$this->getValue(), \PHP_URL_HOST);

            if (!empty($host) && !preg_match("/(?:.+\.|^)(usps|shippingapis)\.com$/i", $host)) {
                throw new ValidatorException(__('USPS API endpoint URL\'s must use usps.com or shippingapis.com'));
            }
        }

        return parent::beforeSave();
    }
}
