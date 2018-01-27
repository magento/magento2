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
use Magento\Inventory\Model\StockSourceLink;

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
     * @param StockSourceLink[] $links
     * @return ValidationResult
     */
    public function validate(array $links): ValidationResult
    {
        $errors = [];
        foreach ($links as $link) {
            if ($link->getStockId() === 1 && $link->getSourceCode() !== 'default') {
                $errors[] = __('Sources can\'t be assigned to Default stock!');
            }
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
