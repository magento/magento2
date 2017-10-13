<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\ResourceModel\Schedule\Expression\Part;

use Magento\Cron\Model\ResourceModel\Schedule\Expression\PartInterface;

/**
 * Cron expression part validator
 *
 * @api
 */
class Validator implements ValidatorInterface
{
    /**
     * @var ParserInterface
     */
    private $parser;

    /**
     * @var ValidatorHandlerFactory
     */
    private $validatorHandlerFactory;

    /**
     * Validator constructor.
     *
     * @param ParserInterface         $parser
     * @param ValidatorHandlerFactory $validatorHandlerFactory
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        ParserInterface $parser,
        ValidatorHandlerFactory $validatorHandlerFactory
    ) {
        $this->parser = $parser;
        $this->validatorHandlerFactory = $validatorHandlerFactory;
    }

    /**
     * @param PartInterface $part
     *
     * @return bool
     */
    public function validate(PartInterface $part)
    {
        $check = false;
        foreach ($this->parser->parse($part->getPartValue(), ParserFactory::LIST_PARSER) as $subPartValue) {
            foreach ($part->getValidatorHandlers() as $validatorHandler) {
                $subPartValue = $this->validatorHandlerFactory->create($validatorHandler)->handle($part, $subPartValue);
                if (is_bool($subPartValue)) {
                    $check = $subPartValue;
                    break;
                }
            }

            if (!$check) {
                break;
            }
        }

        return $check;
    }
}
