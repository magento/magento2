<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\MessageGenerators;

/**
 * Common implementation of message generator.
 * Takes a message template (placeholders for localization also can be used) and list
 * of required params, which should persist in input data.
 *
 * If template contains placeholders, when required params should be specified in the same order as
 * placeholders, for example:
 * Message is 'Case Update: New score for the order is %1. Previous score was %2.', then the required params order
 * should be ['new_score', 'prev_score'].
 */
class PatternGenerator implements GeneratorInterface
{
    /**
     * @var string
     */
    private $template;

    /**
     * @var array
     */
    private $requiredParams;

    /**
     * PatternGenerator constructor.
     *
     * @param string $template
     * @param array $requiredParams
     */
    public function __construct($template, array $requiredParams = [])
    {
        $this->template = $template;
        $this->requiredParams = $requiredParams;
    }

    /**
     * @inheritdoc
     */
    public function generate(array $data)
    {
        $placeholders = [];
        foreach ($this->requiredParams as $param) {
            if (empty($data[$param])) {
                throw new GeneratorException(__('The "%1" should not be empty.', $param));
            }
            $placeholders[] = $data[$param];
        }
        return __($this->template, ...$placeholders);
    }
}
