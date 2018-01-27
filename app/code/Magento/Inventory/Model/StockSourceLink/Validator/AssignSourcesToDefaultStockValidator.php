<?php
/**
 * Created by PhpStorm.
 * User: Stepan Furman
 * Date: 27.01.18
 * Time: 15:33
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\StockSourceLink\Validator;


use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;

class AssignSourcesToDefaultStockValidator implements StockSourceLinkValidatorInterface
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
     * @param array $sourceCodes
     * @param int $stockId
     * @return ValidationResult
     */
    public function validate(array $sourceCodes, int $stockId): ValidationResult
    {
        $errors = [];

        if ($stockId === 1 && $this->isNonDefaultSourceInArray($sourceCodes)) {
            $errors[] = __('Sources can\'t be assigned to Default stock!');
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }

    private function isNonDefaultSourceInArray(array $sourceCodes): bool
    {
        return (bool) count($sourceCodes) > 1 || !in_array('default', $sourceCodes);
    }
}
