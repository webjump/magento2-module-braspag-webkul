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
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;

class Edit extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

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
    protected $_mpHelper;

    /**
     * Edit constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Url $url
     * @param \Magento\Customer\Model\Session $session
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Config\ConfigInterface $braspagPaymentSplitConfig
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Url $url,
        \Magento\Customer\Model\Session $session,
        \Webkul\MpAssignProduct\Helper\Data $helper,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Config\ConfigInterface $braspagPaymentSplitConfig
    ) {

        $this->setBraspagPaymentSplitConfig($braspagPaymentSplitConfig);

        $this->_resultPageFactory = $resultPageFactory;
        $this->_url = $url;
        $this->_session = $session;
        $this->_assignHelper = $helper;
        $this->_mpHelper = $mpHelper;
        parent::__construct($context);
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
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
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
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (!$this->getBraspagPaymentSplitConfig()->getPaymentSplitMarketPlaceGeneralBraspagFinancialPageEnabled()) {
            $this->messageManager->addError(__('Invalid Route'));
            return $this->resultRedirectFactory->create()->setPath('customer/account', ['_secure' => $this->getRequest()->isSecure()]);

        } else {

            $resultPage = $this->_resultPageFactory->create();

            if ($this->_mpHelper->getIsSeparatePanel()) {
                $resultPage->addHandle('mpassignproduct_product_edit_layout2');
            }

            $resultPage->getConfig()->getTitle()->set(__('Marketplace Braspag Seller Financial Profile'));

            return $resultPage;
        }

    }
}
