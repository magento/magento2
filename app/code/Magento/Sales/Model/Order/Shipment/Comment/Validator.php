<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Model\Order\Shipment\Comment;

use Magento\Sales\Model\Order\Shipment\Comment;

/**
 * Class Validator
 */
class Validator
{
    /**
     * Required field
     *
     * @var array
     */
    protected $required = [
        'parent_id' =>'Parent Shipment Id',
        'comment' => 'Comment'
    ];

    /**
     * Validate data
     *
     * @param \Magento\Sales\Model\Order\Shipment\Comment $comment
     * @return array
     */
    public function validate(Comment $comment)
    {
        $errors = [];
        $commentData = $comment->getData();
        foreach ($this->required as $code => $label) {
            if (!$comment->hasData($code)) {
                $errors[$code] = sprintf('%s is a required field', $label);
            } elseif (empty($commentData[$code])) {
                $errors[$code] = sprintf('%s can not be empty', $label);
            }
        }

        return $errors;
    }
}
