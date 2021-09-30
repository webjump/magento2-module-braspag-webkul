<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Braspag\Webkul\Block\Financial\Profile;

/**
 * Class Edit
 * @package Braspag\Webkul\Block\Financial\Profile
 */
class Edit extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Config\ConfigInterface
     */
    protected $braspagPaymentSplitConfig;

    /**
     * @var Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Command\GetSubordinateCommand
     */
    protected $getSubordinateCommand;

    /**
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    protected $_assignHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $sessionFactory;

    protected $context;

    protected $customerAddress;

    protected $requestedData;

    /**
     * Edit constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     * @param \Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Config\ConfigInterface $braspagPaymentSplitConfig
     * @param \Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Command\GetSubordinateCommand $getSubordinateCommand
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     * @param \Magento\Customer\Model\SessionFactory $sessionFactory
     * @param \Magento\Customer\Model\Address $customerAddress
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = [],
        \Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Config\ConfigInterface $braspagPaymentSplitConfig,
        \Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Command\GetSubordinateCommand $getSubordinateCommand,
        \Webkul\MpAssignProduct\Helper\Data $helper,
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        \Magento\Customer\Model\Address $customerAddress
    ) {
        $this->setBraspagPaymentSplitConfig($braspagPaymentSplitConfig);
        $this->setGetSubordinateCommand($getSubordinateCommand);

        $this->_assignHelper = $helper;
        $this->sessionFactory = $sessionFactory;
        $this->context = $context;
        $this->customerAddress = $customerAddress;

        $this->requestedData = $this->context->getRequest()->getParams();

        parent::__construct($context, $data);
    }

    protected function _construct() {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('Marketplace Braspag Seller Financial Profile'));
    }

    /**
     * @return Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Config\ConfigInterface
     */
    public function getBraspagPaymentSplitConfig()
    {
        return $this->braspagPaymentSplitConfig;
    }

    /**
     * @param Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Config\ConfigInterface $braspagPaymentSplitConfig
     */
    public function setBraspagPaymentSplitConfig($braspagPaymentSplitConfig)
    {
        $this->braspagPaymentSplitConfig = $braspagPaymentSplitConfig;
    }

    /**
     * @return mixed
     */
    public function getGetSubordinateCommand()
    {
        return $this->getSubordinateCommand;
    }

    /**
     * @param mixed $getSubordinateCommand
     */
    public function setGetSubordinateCommand($getSubordinateCommand)
    {
        $this->getSubordinateCommand = $getSubordinateCommand;
    }

    /**
     * @return array
     */
    public function getRequestedData()
    {
        $data = [];
        if (!empty($this->requestedData['data'])) {
            $encodedData = explode('&', $this->requestedData['data']);

            foreach ($encodedData as $value) {
                $newValue = explode("=", $value);

                if (!isset($newValue[1])) {
                    continue;
                }
                $data[$newValue[0]] = $newValue[1];
            }

            $this->requestedData = $data;
        }

        return $this->requestedData;
    }

    /**
     * @param array $requestedData
     */
    public function setRequestedData($requestedData)
    {
        $this->requestedData = $requestedData;
    }

    /**
     * @return array
     */
    public function getSubordinateDataFromBraspag()
    {
        $customerSession = $this->sessionFactory->create();

        $subordinateId = $customerSession->getCustomer()->getId();

        $marketplaceSubordinateData = $this->_assignHelper->getSellerDetails($subordinateId);

        try {
            if (!empty($marketplaceSubordinateData->getBraspagSubordinateMerchantid())) {
                return $this->getGetSubordinateCommand()->execute([
                    'subordinate' => $subordinateId,
                    'merchantId' => $marketplaceSubordinateData->getBraspagSubordinateMerchantid()
                ]);
            }
        } catch (\Exception $e) {
            return -1;
        }

        return [];
    }

    /**
     * @return \Magento\Customer\Model\Address
     */
    public function getSubordinateBillingAddress()
    {
        $customerSession = $this->sessionFactory->create();

        $billingID = $customerSession->getCustomer()->getDefaultBilling();

        return $this->customerAddress->load($billingID);
    }

    /**
     * @return \Magento\Customer\Model\Address
     */
    public function getSubordinate()
    {
        $customerSession = $this->sessionFactory->create();

        return $customerSession->getCustomer();
    }


}
