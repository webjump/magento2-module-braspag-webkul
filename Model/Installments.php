<?php

namespace Braspag\Webkul\Model;


use Webjump\BraspagPagador\Gateway\Transaction\Base\Resource\Installments\BuilderInterface;
use Webjump\BraspagPagador\Api\InstallmentsInterface;
use Braspag\Webkul\Gateway\Transaction\Base\Resource\Installments\Installment;
use Webjump\BraspagPagador\Model\Installments as WebjumpInstallments;

class Installments extends WebjumpInstallments
{
    /**
     * @return array
     */
    public function getInstallments()
    {
        $result = [];

        /** @var Installment $item */
        foreach ($this->getBuilder()->build() as $item) {
            $result[] = [
                'id' => $item->getId(),
                'label' => $item->getLabel(),
                'price' => $item->getPrice(),
                'interest' => $item->getHasInterest()
            ];
        }

        return $result;
    }
}
