<?php

namespace ThanhVo\SalesRule\Model\Rule\Action\Discount;
use Magento\Framework\App\ObjectManager;
use Magento\SalesRule\Model\Quote\ChildrenValidationLocator;
use Magento\SalesRule\Model\Rule\Action\Discount\AbstractDiscount;
use Magento\SalesRule\Model\Rule\Action\Discount\DataFactory;

class BuyXGetYCross extends AbstractDiscount
{
    const BUY_X_GET_Y_CROSS_ACTION = 'buy_x_get_y_cross';

    /**
     * @var \Magento\SalesRule\Model\Utility
     */
    protected $validatorUtility;

    /**
     * @var ChildrenValidationLocator
     */
    private $childrenValidationLocator;

    /**
     * @param \Magento\SalesRule\Model\Validator $validator
     * @param DataFactory $discountDataFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Magento\SalesRule\Model\Validator $validator,
        \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory $discountDataFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\SalesRule\Model\Utility $utility,
        ChildrenValidationLocator $childrenValidationLocator = null
    ) {
        parent::__construct($validator, $discountDataFactory, $priceCurrency);

        $this->validatorUtility = $utility;
        $this->childrenValidationLocator = $childrenValidationLocator
            ?: ObjectManager::getInstance()->get(ChildrenValidationLocator::class);
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data
     */
    public function calculate($rule, $item, $qty)
    {
        $quote = $item->getQuote();

        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $discountData = $this->discountFactory->create();

        $itemPrice = $this->validator->getItemPrice($item);
        $baseItemPrice = $this->validator->getItemBasePrice($item);
        $itemOriginalPrice = $this->validator->getItemOriginalPrice($item);
        $baseItemOriginalPrice = $this->validator->getItemBaseOriginalPrice($item);

        $x = $rule->getDiscountStep();
        $y = $rule->getDiscountAmount();
        if (!$x || $y > $x) {
            return $discountData;
        }
        $buyAndDiscountQty = $x + $y;

        $crossQty = $this->getCrossQty($quote, $rule);

        $fullRuleQtyPeriod = floor($crossQty / $buyAndDiscountQty);
        $freeQty = $crossQty - $fullRuleQtyPeriod * $buyAndDiscountQty;

        $discountedQty = $quote->getDiscountedQty() ?: 0;

        $discountQty = $fullRuleQtyPeriod * $y - $discountedQty;
        if ($discountQty > $qty) {
            $discountQty = $qty;
        }

        if ($freeQty > $x) {
            $discountQty += $freeQty - $x;
        }

        if ($discountQty < 0) {
            $discountQty = 0;
        }

        $quote->setDiscountedQty($discountedQty + $discountQty);

        $discountData->setAmount($discountQty * $itemPrice);
        $discountData->setBaseAmount($discountQty * $baseItemPrice);
        $discountData->setOriginalAmount($discountQty * $itemOriginalPrice);
        $discountData->setBaseOriginalAmount($discountQty * $baseItemOriginalPrice);

        return $discountData;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\SalesRule\Model\Rule $rule
     * @return float|int|mixed
     */
    protected function getCrossQty(
        \Magento\Quote\Model\Quote $quote,
        \Magento\SalesRule\Model\Rule $rule
    )
    {
        $crossQty = $quote->getCrossQty();
        if (empty($crossQty)) {
            $crossQty = 0;
            foreach ($quote->getAllVisibleItems() as $item) {
                $address = $item->getAddress();

                if ($item->isDeleted()) {
                    continue;
                }

                if (!$this->validatorUtility->canProcessRule($rule, $address)) {
                    continue;
                }

                if (!$rule->getActions()->validate($item)) {
                    if (!$this->childrenValidationLocator->isChildrenValidationRequired($item)) {
                        continue;
                    }
                    $childItems = $item->getChildren();
                    $isContinue = true;
                    if (!empty($childItems)) {
                        foreach ($childItems as $childItem) {
                            if ($rule->getActions()->validate($childItem)) {
                                $isContinue = false;
                            }
                        }
                    }
                    if ($isContinue) {
                        continue;
                    }
                }

                $crossQty += $item->getQty();
            }

            $quote->setCrossQty($crossQty);
        }

        return $crossQty;
    }
}
