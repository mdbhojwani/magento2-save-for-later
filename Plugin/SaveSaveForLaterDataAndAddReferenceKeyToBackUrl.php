<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Plugin;

use Mdbhojwani\SaveForLater\Model\DataSerializer;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Url\Helper\Data as UrlHelper;

/**
 * Cache SaveForLater data & Modify back Url
 */
class SaveSaveForLaterDataAndAddReferenceKeyToBackUrl
{
    /**
     * @var DataSerializer
     */
    private $dataSerializer;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * @param DataSerializer $dataSerializer
     * @param CustomerSession $customerSession
     * @param UrlInterface $urlBuilder
     * @param UrlHelper $urlHelper
     */
    public function __construct(
        DataSerializer $dataSerializer,
        CustomerSession $customerSession,
        UrlInterface $urlBuilder,
        UrlHelper $urlHelper
    ) {
        $this->dataSerializer = $dataSerializer;
        $this->customerSession = $customerSession;
        $this->urlBuilder = $urlBuilder;
        $this->urlHelper = $urlHelper;
    }

    /**
     * Before sending New Account Email, Cache SaveForLater data & Modify back Url
     *
     * @param EmailNotificationInterface $subject
     * @param CustomerInterface $customer
     * @param string $type
     * @param string $backUrl
     * @param int $storeId
     * @param string $sendemailStoreId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeNewAccount(
        EmailNotificationInterface $subject,
        CustomerInterface $customer,
        $type = EmailNotificationInterface::NEW_ACCOUNT_EMAIL_REGISTERED,
        $backUrl = '',
        $storeId = null,
        $sendemailStoreId = null
    ): array {
        if (($this->customerSession->getBeforeSaveForLaterRequest() != null)
            && ($customer->getConfirmation() != null)
            && ($backUrl !== null && strpos($backUrl, 'saveforlater/index/add') !== false)
        ) {
            $token = $this->dataSerializer->serialize($this->customerSession->getBeforeSaveForLaterRequest());
            $backUrl = $this->urlHelper->addRequestParam($backUrl, ['token' => $token]);
        }

        return [$customer, $type, $backUrl, $storeId, $sendemailStoreId];
    }
}
