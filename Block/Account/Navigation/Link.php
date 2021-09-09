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
namespace Braspag\Webkul\Block\Account\Navigation;

use Magento\Framework\View\Element\Html\Link\Current;
use Webkul\Marketplace\Helper\Data as MpHelper;

class Link extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $context;

    /**
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    protected $defaultPathInterface;

    /**
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    protected $helper;

    /**
     * @var \MpHelper
     */
    protected $mpHelper;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Config\ConfigInterface
     */
    protected $braspagPaymentSplitConfig;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\DefaultPathInterface $defaultPathInterface
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     * @param MpHelper $mpHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPathInterface,
        \Webkul\MpAssignProduct\Helper\Data $helper,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        MpHelper $mpHelper,
        array $data = [],
        \Webjump\BraspagPagador\Gateway\Transaction\PaymentSplit\Config\ConfigInterface $braspagPaymentSplitConfig
    ) {
        $this->helper = $helper;
        $this->pricingHelper = $pricingHelper;
        $this->mpHelper = $mpHelper;

        $this->setBraspagPaymentSplitConfig($braspagPaymentSplitConfig);

        parent::__construct($context, $defaultPathInterface, $data);
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
     * Render block HTML.
     *
     * @return string
     */
    protected function _toHtml()
    {
        $helper = $this->helper;
        if (!$helper->isSeller()) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * Get current Url
     *
     * @return url
     */
    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl();
    }

    /**
     * Get Helper Object
     *
     * @return object
     */
    public function getHelperObject($helper = '')
    {
        if ($helper == 'marketplaceHelper') {
            return $this->mpHelper;
        } elseif ($helper == 'pricingHelper') {
            return $this->pricingHelper;
        } else {
            return $this->helper;
        }
    }
}
