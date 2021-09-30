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
namespace Braspag\Webkul\Controller\Financial\Profile;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;

/**
 * Class Save
 * @package Webjump\BraspagPagador\Controller\PaymentSplit\SubordinateProfile
 */
class Save extends \Magento\Framework\App\Action\Action
{
    const DEFAULT_STORE_ID = 0;
    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_url;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_session;

    /**
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    protected $_assignHelper;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;

    /**
     * @var Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Command\CreateSubordinateCommand
     */
    protected $createSubordinateCommand;

    /**
     * Save constructor.
     * @param Context $context
     * @param \Magento\Customer\Model\Url $url
     * @param \Magento\Customer\Model\Session $session
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Command\CreateSubordinateCommand $createSubordinateCommand
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Url $url,
        \Magento\Customer\Model\Session $session,
        \Webkul\MpAssignProduct\Helper\Data $helper,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Command\CreateSubordinateCommand $createSubordinateCommand
    ) {
        $this->_url = $url;
        $this->_session = $session;
        $this->_assignHelper = $helper;
        $this->mpHelper = $mpHelper;
        $this->createSubordinateCommand = $createSubordinateCommand;

        parent::__construct($context);
    }

    /**
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_url->getLoginUrl();
        if (!$this->_session->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $helper = $this->mpHelper;

        $data = $this->getRequest()->getParams();

        if (!$helper->isSeller()) {
            $this->messageManager->addErrorMessage(__('Invalid Route'));
            return $this->resultRedirectFactory->create()->setPath('customer/account/index', ['_secure' => $this->getRequest()->isSecure()]);
        }

        $subordinateId = $this->_session->getCustomerId();

        $marketplaceSubordinateData = $this->_assignHelper->getSellerDetails($subordinateId);

        if (!empty($marketplaceSubordinateData->getBraspagSubordinateMerchantid())) {
            $this->messageManager->addError(__('Subordinate Already Sent to Braspag'));
            return $this->resultRedirectFactory->create()->setPath('braspagmarketplace/financial_profile/edit');
        }

        try {
            $this->createSubordinateCommand->execute([
                'subordinate' => $subordinateId,
                'data' => $data
            ]);

        } catch (\Exception $e) {

            $this->messageManager->addErrorMessage(__('Error on sent subordinate data in Braspag.'));

            $this->messageManager->addErrorMessage($e->getMessage());

            $encodeData = "";
            foreach ($data as $key => $value) {
                $encodeData .= "$key=$value&";
            }

            return $this->resultRedirectFactory->create()->setPath('braspagmarketplace/financial_profile/edit', [
                '_secure' => $this->getRequest()->isSecure(),
                'data' => $encodeData
            ]);
        }

        $this->messageManager->addSuccessMessage(__('Subordinate is sent to Braspag successfully.'));

        return $this->resultRedirectFactory->create()->setPath('braspagmarketplace/financial_profile/edit', [
            '_secure' => $this->getRequest()->isSecure(),
            'data' => 'success=1'
        ]);

    }
}
