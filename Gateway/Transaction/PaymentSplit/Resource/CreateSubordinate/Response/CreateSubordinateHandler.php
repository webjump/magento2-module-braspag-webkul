<?php

namespace Braspag\Webkul\Gateway\Transaction\PaymentSplit\Resource\CreateSubordinate\Response;

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
class CreateSubordinateHandler extends AbstractHandler implements HandlerInterface
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
     * @return array|CreateSubordinateHandler|mixed|void|\Webjump\BraspagPagador\Gateway\Transaction\Base\Resource\Response\ResponseInterface
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (!isset($response['response']) || !$response['response'] instanceof $this->response) {
            throw new \InvalidArgumentException('Braspag Response Lib object should be provided');
        }

        $response = $response['response'];

        if (!isset($handlingSubject['subordinate']) || empty($handlingSubject['subordinate'])) {
            throw new \InvalidArgumentException('Subordinate data object should be provided');
        }

        $subordinateId = $handlingSubject['subordinate'];

        $response = $this->_handle($handlingSubject, $response);

        $subordinate = $this->getMarketplaceHelperData()
            ->getSellerCollectionObj($subordinateId)->getFirstItem();

        $subordinate->setBraspagSubordinateMerchantid($response->getMerchantId());
        $subordinate->setBraspagSubordinateStatus('UnderAnalysis');

        $subordinate->save();

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
