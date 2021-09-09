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
     * Edit constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     * @param \Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Config\ConfigInterface $braspagPaymentSplitConfig
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = [],
        \Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Config\ConfigInterface $braspagPaymentSplitConfig
    ) {
        $this->setBraspagPaymentSplitConfig($braspagPaymentSplitConfig);

        parent::__construct($context, $data);
    }

    protected function _construct() {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('Marketplace Braspag Financial Profile'));
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
}
