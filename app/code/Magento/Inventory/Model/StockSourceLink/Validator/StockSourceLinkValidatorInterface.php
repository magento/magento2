<?php
/**
 * User: Stepan Furman
 * Date: 27.01.18
 * Time: 15:35
 */

declare(strict_types=1);

namespace Magento\Inventory\Model\StockSourceLink\Validator;

use Magento\Framework\Validation\ValidationResult;
use Magento\Inventory\Model\StockSourceLink;

/**
 * Responsible for StockSourceLink validation
 * Extension point for base validation
 *
 * @api
 */
interface StockSourceLinkValidatorInterface
{
    /**
     * @param StockSourceLink[] $links
     * @return ValidationResult
     */
    public function validate(array $links): ValidationResult;
}
