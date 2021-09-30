<?php

namespace Braspag\Webkul\Gateway\Transaction\PaymentSplit\Resource\GetSubordinate\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Webjump\Braspag\Pagador\Transaction\Resource\PaymentSplit\GetSubordinate\Response;
use Webjump\BraspagPagador\Gateway\Transaction\Base\Resource\Response\AbstractHandler;
use Webjump\BraspagPagador\Model\SplitManager;
use Webjump\BraspagPagador\Model\SplitDataAdapter;

/**

 * Braspag Transaction Response Handler
 *
 * @author      Webjump Core Team <dev@webjump.com>
 * @copyright   2016 Webjump (http://www.webjump.com.br)
 * @license     http://www.webjump.com.br  Copyright
 *
 * @link        http://www.webjump.com.br
 */
class GetSubordinateHandler extends AbstractHandler implements HandlerInterface
{
    protected $splitManager;

    /**
     * @var
     */
    protected $splitAdapter;

    protected $marketplaceHelperData;

    public function __construct(
        SplitManager $splitManager,
        Response $response,
        SplitDataAdapter $splitAdapter,
        \Webkul\Marketplace\Helper\Data $marketplaceHelperData
    ) {
        $this->setSplitManager($splitManager);
        $this->setResponse($response);
        $this->setSplitAdapter($splitAdapter);
        $this->setMarketplaceHelperData($marketplaceHelperData);
    }

    /**
     * @return Webjump\BraspagPagador\Model\SplitManager
     */
    public function getSplitManager(): SplitManager
    {
        return $this->splitManager;
    }

    /**
     * @param Webjump\BraspagPagador\Model\SplitManager $splitManager
     */
    public function setSplitManager(SplitManager $splitManager)
    {
        $this->splitManager = $splitManager;
    }

    /**
     * @return mixed
     */
    public function getObjectFactory()
    {
        return $this->objectFactory;
    }

    /**
     * @param mixed $objectFactory
     */
    public function setObjectFactory($objectFactory)
    {
        $this->objectFactory = $objectFactory;
    }

    /**
     * @return mixed
     */
    public function getSplitAdapter()
    {
        return $this->splitAdapter;
    }

    /**
     * @param mixed $splitAdapter
     */
    public function setSplitAdapter($splitAdapter)
    {
        $this->splitAdapter = $splitAdapter;
    }

    /**
     * @return mixed
     */
    public function getMarketplaceHelperData()
    {
        return $this->marketplaceHelperData;
    }

    /**
     * @param mixed $marketplaceHelperData
     */
    public function setMarketplaceHelperData($marketplaceHelperData)
    {
        $this->marketplaceHelperData = $marketplaceHelperData;
    }

    /**
     * @param array $handlingSubject
     * @param array $response
     * @return array|GetSubordinateHandler|mixed|void|\Webjump\BraspagPagador\Gateway\Transaction\Base\Resource\Response\ResponseInterface
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (!isset($response['response']) || !$response['response'] instanceof $this->response) {
            throw new \InvalidArgumentException('Braspag Response Lib object should be provided');
        }

        $response = $response['response'];

        $subordinateId = $handlingSubject['subordinate'];
        $subordinateMerchantId = $handlingSubject['merchantId'];
        $response = $this->_handle($handlingSubject, $response);
        $subordinate = null;

        if (empty($subordinateId)) {
            $subordinateCollection = $this->getMarketplaceHelperData()->getSellerCollection();
            $subordinateCollection->addFieldToFilter('braspag_subordinate_merchantid', $subordinateMerchantId);

            $subordinate = $subordinateCollection->getFirstItem();

            if(empty($subordinate->getId())) {
                throw new \InvalidArgumentException('Invalid Subordinate MerchantId');
            }

            $subordinateId = $subordinate->getSellerId();
        }

        if (empty($subordinate)) {
            $subordinate = $this->getMarketplaceHelperData()
                ->getSellerCollectionObj($subordinateId)->getFirstItem();
        }

        if (!empty($response->getAnalysisStatus())) {
            $subordinate->setBraspagSubordinateStatus($response->getAnalysisStatus());
            $subordinate->save();
        }

        return $response;
    }

    /**,
     * @param $handlingSubject
     * @param $response
     * @return $this
     */
    protected function _handle($handlingSubject, $response)
    {
        if (!$response) {
            return $this;
        }

        return $response;
    }
}
