<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ThemeGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Theme\Model\Favicon\Favicon;

class HeadShortcutIconResolver implements ResolverInterface
{
    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var Favicon
     */
    private $favicon;

    /**
     * @param ValueFactory $valueFactory
     */
    public function __construct(ValueFactory $valueFactory, Favicon $favicon)
    {
        $this->valueFactory = $valueFactory;
        $this->favicon = $favicon;
    }

    /**
     * Fetch and format configurable variants.
     *
     * {@inheritdoc}
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $result = function () {
            $value = $this->favicon->getFaviconFile();
            if ($value === false) {
                $value = null;
            }
            return $value;
        };

        return $this->valueFactory->create($result);
    }
}