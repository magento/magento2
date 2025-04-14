<?php
/************************************************************************
 * Copyright 2025 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ***********************************************************************
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order\Creditmemo\Comment;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\Order\Creditmemo\Comment;
use Magento\Sales\Helper\SalesEntityCommentValidator;

/**
 * Sales credit memo comment validator
 */
class Validator
{
    /**
     * Sales entity comment validator
     * @var SalesEntityCommentValidator
     */
    private SalesEntityCommentValidator $helperValidator;

    /**
     * Required field
     *
     * @var array
     */
    protected $required = [
        'parent_id' => 'Parent Creditmemo Id',
        'comment' => 'Comment',
    ];

    /**
     * @param SalesEntityCommentValidator|null $helperValidator
     */
    public function __construct(
        ?SalesEntityCommentValidator $helperValidator = null
    ) {
        $this->helperValidator = $helperValidator ??
            ObjectManager::getInstance()->get(SalesEntityCommentValidator::class);
    }

    /**
     * Validate data
     *
     * @param Comment $comment
     * @return array
     */
    public function validate(Comment $comment)
    {
        $errors = [];
        $commentData = $comment->getData();

        if (!$this->helperValidator->isEditCommentAllowed($comment)) {
            $errors['comment'] = sprintf('User is not authorized to edit comment.');
        }

        foreach ($this->required as $code => $label) {
            if (!$comment->hasData($code)) {
                $errors[$code] = sprintf('"%s" is required. Enter and try again.', $label);
            } elseif (empty($commentData[$code])) {
                $errors[$code] = sprintf('%s can not be empty', $label);
            }
        }

        return $errors;
    }
}
