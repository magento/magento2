<?php

declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model\Product\Attribute\Backend;

use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\ResourceModel\Product\IsUniqueUrlKey;

class UrlKey extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var IsUniqueUrlKey
     */
    private $urlKey;

    /**
     * @param IsUniqueUrlKey $urlKey
     */
    public function __construct(
        IsUniqueUrlKey $urlKey
    ) {
        $this->urlKey = $urlKey;
    }

    /**
     * @param  Product $object
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate($object)
    {
        $urlKeyValue = $object->getUrlKey() !== null ? $object->getUrlKey(): $object->formatUrlKey($object->getName());
        if ($this->urlKey->execute($urlKeyValue)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Url key value: "%1" already taken. Please insert different url key.', $urlKeyValue)
            );
        }
        return true;
    }
}
