<?php

namespace ThanhVo\SalesRule\Plugin\Model;

use Magento\Quote\Model\Quote\Address;

class Validator
{
    /**
     * @param \Magento\SalesRule\Model\Validator $subject
     * @param array $items
     * @param Address|null $address
     * @return mixed
     */
    public function beforeSortItemsByPriority(
        \Magento\SalesRule\Model\Validator $subject,
        $items,
        Address $address = null
    )
    {
        usort($items, function($a, $b) use ($subject)
        {
            return strcmp($subject->getItemPrice($a), $subject->getItemPrice($b));
        });

        return [$items, $address];
    }
}