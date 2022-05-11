<?php

namespace Braspag\Webkul\Gateway\Transaction\Base\Resource\Installments;

use Webjump\BraspagPagador\Gateway\Transaction\Base\Resource\Installments\InstallmentInterface;
use Magento\Framework\Pricing\Helper\Data;
use Webjump\BraspagPagador\Gateway\Transaction\Base\Resource\Installments\Installment as WebjumpInstallment;

class Installment extends WebjumpInstallment
{
    protected $formattedPrice;

    public function getLabel()
    {
        $interest = __('without interest');

        if ($this->interest) {
            $interest = __('with interest*');
        }

        return "{$this->index}x {$this->formattedPrice} {$interest}";
    }

    public function setPrice($price)
    {
        $this->price = $price;
        $this->formattedPrice = $this->getPriceHelper()->currency($price, true, false);
    }

    public function getPrice() {
        return $this->price;
    }

    public function getHasInterest() {
        return (bool) $this->interest;
    }
}
