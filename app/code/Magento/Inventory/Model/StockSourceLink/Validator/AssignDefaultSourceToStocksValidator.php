<?php
/**
 * Created by PhpStorm.
 * User: Stepan Furman
 * Date: 27.01.18
 * Time: 16:12
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\StockSourceLink\Validator;

use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Inventory\Model\StockSourceLink;

class AssignDefaultSourceToStocksValidator implements StockSourceLinkValidatorInterface
{
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @param ValidationResultFactory $validationResultFactory
     */
    public function __construct(ValidationResultFactory $validationResultFactory)
    {
        $this->validationResultFactory = $validationResultFactory;
    }

    /**
     * @param StockSourceLink[] $links
     * @return ValidationResult
     */
    public function validate(array $links): ValidationResult
    {
        $errors = [];
        foreach ($links as $link) {
            if ($link->getSourceCode() === 'default' && $link->getStockId() !== 1) {
                $errors[] = __('Default Source can\'t be assigned to stock!');
            }
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
