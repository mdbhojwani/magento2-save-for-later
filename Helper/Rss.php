<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Helper;

/**
 * SaveForLater rss helper
 */
class Rss extends \Mdbhojwani\SaveForLater\Helper\Data
{
    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface
     */
    protected $_customer;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Mdbhojwani\SaveForLater\Model\SaveForLaterFactory $saveForLaterFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Customer\Helper\View $customerViewHelper
     * @param \Mdbhojwani\SaveForLater\Controller\SaveForLaterProviderInterface $saveForLaterProvider
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession,
        \Mdbhojwani\SaveForLater\Model\SaveForLaterFactory $saveForLaterFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Customer\Helper\View $customerViewHelper,
        \Mdbhojwani\SaveForLater\Controller\SaveForLaterProviderInterface $saveForLaterProvider,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_customerRepository = $customerRepository;

        parent::__construct(
            $context,
            $coreRegistry,
            $customerSession,
            $saveForLaterFactory,
            $storeManager,
            $postDataHelper,
            $customerViewHelper,
            $saveForLaterProvider,
            $productRepository
        );
    }

    /**
     * Retrieve SaveForLater model
     *
     * @return \Mdbhojwani\SaveForLater\Model\SaveForLater
     */
    public function getSaveForLater()
    {
        if ($this->saveForLater === null) {
            $this->saveForLater = $this->saveForLaterFactory->create();

            $saveForLater = $this->_getRequest()->getParam('saveforlater_id');
            if ($saveForLaterId) {
                $this->saveForLater->load($saveForLaterId);
            } else {
                if ($this->getCustomer()->getId()) {
                    $this->saveForLater->loadByCustomerId($this->getCustomer()->getId());
                }
            }
        }
        return $this->saveForLater;
    }

    /**
     * Retrieve Customer instance
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomer()
    {
        if ($this->_customer === null) {
            $params = $this->urlDecoder->decode($this->_getRequest()->getParam('data', ''));
            $data = explode(',', $params);
            $customerId = abs((int)$data[0]);
            if ($customerId && ($customerId == $this->_customerSession->getCustomerId())) {
                $this->_customer = $this->_customerRepository->getById($customerId);
            } else {
                $this->_customer = $this->_customerFactory->create();
            }
        }

        return $this->_customer;
    }

    /**
     * Is allow RSS
     *
     * @return bool
     */
    public function isRssAllow()
    {
        return $this->_moduleManager->isEnabled('Magento_Rss')
            && $this->scopeConfig->isSetFlag(
                'rss/saveforlater/active',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }
}
