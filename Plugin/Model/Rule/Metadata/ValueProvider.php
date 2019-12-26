<?php

namespace ThanhVo\SalesRule\Plugin\Model\Rule\Metadata;

use ThanhVo\SalesRule\Model\Rule\Action\Discount\BuyXGetYCross;

class ValueProvider
{
    /**
     * @param \Magento\SalesRule\Model\Rule\Metadata\ValueProvider $subject
     * @param $result
     * @return mixed
     */
    public function afterGetMetadataValues(
        \Magento\SalesRule\Model\Rule\Metadata\ValueProvider $subject,
        $result
    ) {
        $applyOptions = ['label' => __('Buy X get Y free cross (discount amount is Y)'), 'value' => BuyXGetYCross::BUY_X_GET_Y_CROSS_ACTION];
        array_push($result['actions']['children']['simple_action']['arguments']['data']['config']['options'], $applyOptions);
        return $result;
    }
}