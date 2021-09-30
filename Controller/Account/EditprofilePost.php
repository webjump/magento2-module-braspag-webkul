<?php
/**
 * @author      Webjump Core Team <dev@webjump.com.br>
 * @copyright   2021 Webjump (http://www.webjump.com.br)
 * @license     http://www.webjump.com.br  Copyright
 *
 * @link        http://www.webjump.com.br
 */

namespace Braspag\Webkul\Controller\Account;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Customer\Model\Url as CustomerUrl;
use Webkul\Marketplace\Model\SellerFactory;

/**
 * Webkul Marketplace Account EditprofilePost Controller.
 */
class EditprofilePost extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * File Uploader factory.
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $helper;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var CustomerUrl
     */
    protected $customerUrl;

    /**
     * @var SellerFactory
     */
    protected $sellerModel;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerModel;

    /**
     * @param Context                                          $context
     * @param Session                                          $customerSession
     * @param FormKeyValidator                                 $formKeyValidator
     * @param \Magento\Framework\Stdlib\DateTime\DateTime      $date
     * @param Filesystem                                       $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Webkul\Marketplace\Helper\Data                  $helper
     * @param DataPersistorInterface                           $dataPersistor
     * @param CustomerUrl                                      $customerUrl
     * @param SellerFactory                                    $sellerModel
     * @param \Magento\Customer\Model\CustomerFactory          $customerModel
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Webkul\Marketplace\Helper\Data $helper,
        DataPersistorInterface $dataPersistor,
        CustomerUrl $customerUrl = null,
        SellerFactory $sellerModel = null,
        \Magento\Customer\Model\CustomerFactory $customerModel = null
    ) {
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_date = $date;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->helper = $helper;
        $this->dataPersistor = $dataPersistor;
        $this->customerUrl = $customerUrl ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->create(CustomerUrl::class);
        $this->sellerModel = $sellerModel ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->create(SellerFactory::class);
        $this->customerModel = $customerModel ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->create(\Magento\Customer\Model\CustomerFactory::class);
        parent::__construct(
            $context
        );
    }

    /**
     * Retrieve customer session object.
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->_customerSession;
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
        $loginUrl = $this->customerUrl->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Update Seller Profile Informations.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($this->getRequest()->isPost()) {
            try {
                if (!$this->_formKeyValidator->validate($this->getRequest())) {
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/editProfile',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }

                $fields = $this->getRequest()->getParams();
                $errors = $this->validateprofiledata($fields);
                $sellerId = $this->helper->getCustomerId();
                $storeId = $this->helper->getCurrentStoreId();
                $img1 = '';
                $img2 = '';
                if (empty($errors)) {
                    $autoId = 0;
                    $collection = $this->sellerModel->create()
                        ->getCollection()
                        ->addFieldToFilter('seller_id', $sellerId)
                        ->addFieldToFilter('store_id', $storeId);
                    foreach ($collection as $value) {
                        $autoId = $value->getId();
                    }
                    $fields = $this->getSellerProfileFields($fields);
                    // If seller data doesn't exist for current store
                    if (!$autoId) {
                        $sellerDefaultData = [];
                        $collection = $this->sellerModel->create()
                            ->getCollection()
                            ->addFieldToFilter('seller_id', $sellerId)
                            ->addFieldToFilter('store_id', 0);
                        foreach ($collection as $value) {
                            $sellerDefaultData = $value->getData();
                        }
                        foreach ($sellerDefaultData as $key => $value) {
                            if (empty($fields[$key]) && $key != 'entity_id') {
                                $fields[$key] = $value;
                            }
                        }
                    }

                    // Save seller data for current store
                    $value = $this->sellerModel->create()->load($autoId);
                    $value->addData($fields);
                    if (!$autoId) {
                        $value->setCreatedAt($this->_date->gmtDate());
                    }
                    $value->setUpdatedAt($this->_date->gmtDate());
                    $value->save();

                    if ($fields['company_description']) {
                        $fields['company_description'] = str_replace(
                            'script',
                            '',
                            $fields['company_description']
                        );
                    }
                    $value->setCompanyDescription($fields['company_description']);

                    if (isset($fields['return_policy'])) {
                        $fields['return_policy'] = str_replace(
                            'script',
                            '',
                            $fields['return_policy']
                        );
                        $value->setReturnPolicy($fields['return_policy']);
                    }

                    if (isset($fields['shipping_policy'])) {
                        $fields['shipping_policy'] = str_replace(
                            'script',
                            '',
                            $fields['shipping_policy']
                        );
                        $value->setShippingPolicy($fields['shipping_policy']);
                    }

                    if (isset($fields['privacy_policy'])) {
                        $fields['privacy_policy'] = str_replace(
                            'script',
                            '',
                            $fields['privacy_policy']
                        );
                        $value->setPrivacyPolicy($fields['privacy_policy']);
                    }

                    $value->setMetaDescription($fields['meta_description']);

                    /**
                     * set taxvat number for seller
                     */
                    if ($fields['taxvat']) {
                        $customer = $this->customerModel->create()->load($sellerId);
                        $customer->setTaxvat($fields['taxvat']);
                        $customer->setId($sellerId)->save();
                    }

                    $target = $this->_mediaDirectory->getAbsolutePath('avatar/');
                    try {
                        /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
                        $uploader = $this->_fileUploaderFactory->create(
                            ['fileId' => 'banner_pic']
                        );
                        $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                        $uploader->setAllowRenameFiles(true);
                        $result = $uploader->save($target);
                        if ($result['file']) {
                            $value->setBannerPic($result['file']);
                        }
                    } catch (\Exception $e) {
                        $this->helper->logDataInLogger(
                            "Controller_Account_EditProfilePost execute : ".$e->getMessage()
                        );

                        if ($e->getMessage() != 'The file was not uploaded.') {
                            $this->messageManager->addError($e->getMessage());
                            $this->dataPersistor->set('seller_profile_data', $fields);
                        }
                    }
                    try {
                        /** @var $uploaderLogo \Magento\MediaStorage\Model\File\Uploader */
                        $uploaderLogo = $this->_fileUploaderFactory->create(
                            ['fileId' => 'logo_pic']
                        );
                        $uploaderLogo->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                        $uploaderLogo->setAllowRenameFiles(true);
                        $resultLogo = $uploaderLogo->save($target);
                        if ($resultLogo['file']) {
                            $value->setLogoPic($resultLogo['file']);
                        }
                    } catch (\Exception $e) {
                        $this->helper->logDataInLogger(
                            "Controller_Account_EditProfilePost execute : ".$e->getMessage()
                        );
                        if ($e->getMessage() != 'The file was not uploaded.') {
                            $this->messageManager->addError($e->getMessage());
                            $this->dataPersistor->set('seller_profile_data', $fields);
                        }
                    }

                    if (array_key_exists('country_pic', $fields)) {
                        $value->setCountryPic($fields['country_pic']);
                    }
                    $value->save();

                    if (array_key_exists('country_pic', $fields)) {
                        $value->setCountryPic($fields['country_pic']);
                    }
                    $value->setStoreId($storeId);
                    $value->save();
                    try {
                        // clear cache
                        $this->helper->clearCache();
                        if (!empty($errors)) {
                            foreach ($errors as $message) {
                                $this->messageManager->addError($message);
                            }
                            $this->dataPersistor->set('seller_profile_data', $fields);
                        } else {
                            $this->messageManager->addSuccess(
                                __('Profile information was successfully saved')
                            );
                            $this->dataPersistor->clear('seller_profile_data');
                        }

                        return $this->resultRedirectFactory->create()->setPath(
                            '*/*/editProfile',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    } catch (\Exception $e) {
                        $this->helper->logDataInLogger(
                            "Controller_Account_EditProfilePost execute : ".$e->getMessage()
                        );
                        $this->messageManager->addException($e, __('We can\'t save the customer.'));
                    }

                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/editProfile',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                } else {
                    foreach ($errors as $message) {
                        $this->messageManager->addError($message);
                    }
                    $this->dataPersistor->set('seller_profile_data', $fields);

                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/editProfile',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
            } catch (\Exception $e) {
                $this->helper->logDataInLogger(
                    "Controller_Account_EditProfilePost execute : ".$e->getMessage()
                );
                $this->messageManager->addError($e->getMessage());
                $this->dataPersistor->set('seller_profile_data', $fields);

                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/editProfile',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/editProfile',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    protected function validateprofiledata(&$fields)
    {
        $errors = [];
        $data = [];
        foreach ($fields as $code => $value) {
            switch ($code) :
                case 'twitter_id':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)
                    ) {
                        $errors[] = __('Twitter Id cannot contain space and special characters, allowed special characters are @,#,_,-');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'facebook_id':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)
                    ) {
                        $errors[] = __('Facebook Id cannot contain space and special characters, allowed special characters are @,#,_,-');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'instagram_id':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)
                    ) {
                        $errors[] = __('Instagram Id cannot contain space and special characters, allowed special characters are @,#,_,-');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'gplus_id':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)
                    ) {
                        $errors[] = __('Google Plus Id cannot contain space and special characters, allowed special characters are @,#,_,-');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'youtube_id':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)
                    ) {
                        $errors[] = __('Youtube ID cannot contain space and special characters, allowed special characters are @,#,_,-');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'vimeo_id':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)
                    ) {
                        $errors[] = __('Vimeo ID cannot contain space and special characters, allowed special characters are @,#,_,-');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'pinterest_id':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)
                    ) {
                        $errors[] = __('Pinterest ID cannot contain space and special characters, allowed special characters are @,#,_,-');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'moleskine_id':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)
                    ) {
                        $errors[] = __('Moleskine ID cannot contain space and special characters, allowed special characters are @,#,_,-');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'taxvat':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{@#~?><>, |=_+¬-]/', $value)
                    ) {
                        $errors[] = __('Tax/VAT Number cannot contain space and special characters');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'shop_title':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    break;
                case 'contact_number':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    break;
                case 'company_locality':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    break;
                case 'company_description':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $value = $this->helper->validateXssString($value);
                        $fields[$code] = $value;
                    break;
                case 'meta_keyword':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $value = $this->helper->validateXssString($value);
                        $fields[$code] = $value;
                    break;
                case 'meta_description':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $value = $this->helper->validateXssString($value);
                        $fields[$code] = $value;
                    break;
                case 'shipping_policy':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $value = $this->helper->validateXssString($value);
                        $fields[$code] = $value;
                    break;
                case 'privacy_policy':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $value = $this->helper->validateXssString($value);
                        $fields[$code] = $value;
                    break;
                case 'return_policy':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $value = $this->helper->validateXssString($value);
                        $fields[$code] = $value;
                    break;
                case 'braspag_subordinate_merchantid':
                    $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                    $value = $this->helper->validateXssString($value);
                    $fields[$code] = $value;
                    break;
                case 'braspag_subordinate_mdr':
                    $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                    $value = $this->helper->validateXssString($value);
                    $fields[$code] = $value;
                    break;
                case 'braspag_subordinate_fee':
                    $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                    $value = $this->helper->validateXssString($value);
                    $fields[$code] = $value;
                    break;
                case 'braspag_subordinate_status':
                    $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                    $value = $this->helper->validateXssString($value);
                    $fields[$code] = $value;
                    break;
                case 'background_width':
                    if (trim($value) != '' &&
                        strlen($value) != 6 &&
                        substr($value, 0, 1) != '#'
                    ) {
                        $errors[] = __('Invalid Background Color');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
            endswitch;
        }

        return $errors;
    }

    protected function getSellerProfileFields($fields = [])
    {
        if (!isset($fields['tw_active'])) {
            $fields['tw_active'] = 0;
        }
        if (!isset($fields['fb_active'])) {
            $fields['fb_active'] = 0;
        }
        if (!isset($fields['gplus_active'])) {
            $fields['gplus_active'] = 0;
        }
        if (!isset($fields['youtube_active'])) {
            $fields['youtube_active'] = 0;
        }
        if (!isset($fields['vimeo_active'])) {
            $fields['vimeo_active'] = 0;
        }
        if (!isset($fields['instagram_active'])) {
            $fields['instagram_active'] = 0;
        }
        if (!isset($fields['pinterest_active'])) {
            $fields['pinterest_active'] = 0;
        }
        if (!isset($fields['moleskine_active'])) {
            $fields['moleskine_active'] = 0;
        }
        return $fields;
    }
}
