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
     * @param array $sourceCodes
     * @param int $stockId
     * @return ValidationResult
     */
    public function validate(array $sourceCodes, int $stockId): ValidationResult
    {
        $errors = [];

        if (in_array('default', $sourceCodes) && $stockId !== 1) {
            $errors[] = __('Default Source can\'t be assigned to stock!');
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
