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
        // Sort to apply discount on cheapest items first
        usort($items, function($a, $b) use ($subject)
        {
            return $subject->getItemPrice($a) <=> $subject->getItemPrice($b);
        });

        // Reset data for cross cart price rule
        $address->getQuote()->setCrossQty(0);
        $address->getQuote()->setDiscountedQty(0);

        return [$items, $address];
    }
}
