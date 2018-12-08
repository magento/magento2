<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Model\System\Config\Backend;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\App\Config\ScopeConfigInterface;
<<<<<<< HEAD
use Magento\Framework\Exception\LocalizedException;
=======
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3

/**
 * Backend model for processing Public content cache lifetime settings.
 *
 * Class Ttl
 */
class Ttl extends \Magento\Framework\App\Config\Value
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
<<<<<<< HEAD
=======
     * Ttl constructor.
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param Escaper|null $escaper
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
<<<<<<< HEAD
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        Escaper $escaper = null
=======
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        ?Escaper $escaper = null
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->escaper = $escaper ?: ObjectManager::getInstance()->create(Escaper::class);
    }

    /**
<<<<<<< HEAD
     * Throw exception if Ttl data is invalid or empty.
=======
     * Throw exception if Ttl data is invalid or empty
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if ($value < 0 || !preg_match('/^[0-9]+$/', $value)) {
<<<<<<< HEAD
            throw new LocalizedException(
=======
            throw new \Magento\Framework\Exception\LocalizedException(
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
                __(
                    'Ttl value "%1" is not valid. Please use only numbers equal or greater than zero.',
                    $this->escaper->escapeHtml($value)
                )
            );
        }

        return $this;
    }
}
