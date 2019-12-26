<?php

namespace ThanhVo\SalesRule\Plugin\Model\Rule\Action\Discount;
use ThanhVo\SalesRule\Model\Rule\Action\Discount\BuyXGetYCross;

class CalculatorFactory
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory $subject
     * @param callable $proceed
     * @param $type
     * @return mixed
     */
    public function aroundCreate(
        \Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory $subject,
        callable $proceed,
        $type
    )
    {
        if ($type === BuyXGetYCross::BUY_X_GET_Y_CROSS_ACTION) {
            return $this->_objectManager->create(BuyXGetYCross::class);
        } else {
            return $proceed($type);
        }
    }
}