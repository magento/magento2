<?php
/**
 * Created by PhpStorm.
 * User: Stepan Furman
 * Date: 27.01.18
 * Time: 15:31
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\StockSourceLink\Validator;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Inventory\Model\StockSourceLink;

/**
 * Chain of validators. Extension point for new validators via di configuration
 */
class ValidatorChain implements StockSourceLinkValidatorInterface
{
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @var StockSourceLinkValidatorInterface[]
     */
    private $validators;

    /**
     * @param ValidationResultFactory $validationResultFactory
     * @param StockSourceLinkValidatorInterface[] $validators
     * @throws LocalizedException
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        array $validators = []
    ) {
        $this->validationResultFactory = $validationResultFactory;

        foreach ($validators as $validator) {
            if (!$validator instanceof StockSourceLinkValidatorInterface) {
                throw new LocalizedException(
                    __('Source Validator must implement StockSourceLinkValidatorInterface.')
                );
            }
        }
        $this->validators = $validators;
    }

    /**
     * @param StockSourceLink[] $links
     * @return ValidationResult
     */
    public function validate(array $links): ValidationResult
    {
        $errors = [];
        foreach ($this->validators as $validator) {
            $validationResult = $validator->validate($links);

            if (!$validationResult->isValid()) {
                $errors = array_merge($errors, $validationResult->getErrors());
            }
        }
        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
