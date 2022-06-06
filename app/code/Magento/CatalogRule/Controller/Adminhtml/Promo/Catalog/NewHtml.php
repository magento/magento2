<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog;

use Magento\Backend\App\Action\Context;
use Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog as CatalogAction;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\Filter\Date;

abstract class NewHtml extends CatalogAction
{
    /**
     * @var string
     */
    protected string $typeChecked = '';

    /**
     * @var SerializerInterface
     */
    protected SerializerInterface $serializer;

    public function __construct(
        Context             $context,
        Registry            $coreRegistry,
        Date                $dateFilter,
        SerializerInterface $serializer
    ){
        parent::__construct($context, $coreRegistry, $dateFilter);

        $this->serializer   = $serializer;
    }

    /**
     * Verify class instance
     *
     * @param mixed $verifyClass
     * @return bool
     */
    public function verifyClassName($verifyClass): bool
    {
        if ($verifyClass instanceof $this->typeChecked) {
            return true;
        }

        return false;
    }

    /**
     * Get Error json
     *
     * @return bool|string
     */
    protected function getErrorJson()
    {
        return $this->serializer->serialize(
            [
                'error'     => true,
                'message'   => __('Selected type is not inherited from type %1', $this->typeChecked)
            ]
        );
    }
}