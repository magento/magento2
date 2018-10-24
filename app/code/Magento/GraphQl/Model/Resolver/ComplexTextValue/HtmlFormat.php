<?php
/**
 * @author Atwix Team
 * @copyright Copyright (c) 2018 Atwix (https://www.atwix.com/)
 */
declare(strict_types=1);

namespace Magento\GraphQl\Model\Resolver\ComplexTextValue;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * HTML format for complex text value.
 *
 * Initially, a value from parent resolver should be in HTML format, therefore, there is no any customization.
 */
class HtmlFormat implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): ?string {
        if (!isset($value['html'])) {
            throw new GraphQlInputException(__('"html" value should be specified'));
        }

        return $value['html'];
    }
}
