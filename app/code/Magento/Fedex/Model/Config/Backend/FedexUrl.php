<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Fedex\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Validator\Url;

/**
 * Represents a config URL that may point to a Fedex endpoint
 */
class FedexUrl extends Value
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
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param Url $url
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        Url $url,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->url = $url;
    }

    /**
     * @inheritDoc
     *
     * @return AbstractModel
     * @throws ValidatorException
     */
    public function beforeSave(): AbstractModel
    {
        $isValid = $this->url->isValid($this->getValue(), ['http', 'https']);

        if ($isValid) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $host = parse_url((string)$this->getValue(), \PHP_URL_HOST);

            if (!empty($host) && !preg_match('/(?:.+\.|^)fedex\.com$/i', $host)) {
                throw new ValidatorException(__('Fedex API endpoint URL\'s must use fedex.com'));
            }
        }

        return parent::beforeSave();
    }
}
