<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Controller\Index;

use Magento\Captcha\Helper\Data as CaptchaHelper;
use Magento\Captcha\Model\DefaultModel as CaptchaModel;
use Magento\Captcha\Observer\CaptchaStringResolver;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\Action;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Session\Generic as SaveForLaterSession;
use Magento\Framework\Validator\EmailAddress;
use Magento\Framework\Validator\ValidateException;
use Magento\Framework\Validator\ValidatorChain;
use Magento\Framework\View\Result\Layout as ResultLayout;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Send Email SaveForLater Controller
 */
class Send extends \Mdbhojwani\SaveForLater\Controller\AbstractIndex implements Action\HttpPostActionInterface
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var \Magento\Customer\Helper\View
     */
    protected $_customerHelperView;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Mdbhojwani\SaveForLater\Model\Config
     */
    protected $saveForLaterConfig;

    /**
     * @var \Mdbhojwani\SaveForLater\Controller\SaveForLaterProviderInterface
     */
    protected $saveForLaterProvider;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var SaveForLaterSession
     */
    protected $saveForLaterSession;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CaptchaHelper
     */
    private $captchaHelper;

    /**
     * @var CaptchaStringResolver
     */
    private $captchaStringResolver;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Mdbhojwani\SaveForLater\Controller\SaveForLaterProviderInterface $saveForLaterProvider
     * @param \Mdbhojwani\SaveForLater\Model\Config $saveForLaterConfig
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Customer\Helper\View $customerHelperView
     * @param SaveForLaterSession $saveForLaterSession
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param CaptchaHelper|null $captchaHelper
     * @param CaptchaStringResolver|null $captchaStringResolver
     * @param Escaper|null $escaper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Customer\Model\Session $customerSession,
        \Mdbhojwani\SaveForLater\Controller\SaveForLaterProviderInterface $saveForLaterProvider,
        \Mdbhojwani\SaveForLater\Model\Config $saveForLaterConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Customer\Helper\View $customerHelperView,
        SaveForLaterSession $saveForLaterSession,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ?CaptchaHelper $captchaHelper = null,
        ?CaptchaStringResolver $captchaStringResolver = null,
        Escaper $escaper = null
    ) {
        $this->_formKeyValidator = $formKeyValidator;
        $this->_customerSession = $customerSession;
        $this->saveForLaterProvider = $saveForLaterProvider;
        $this->saveForLaterConfig = $saveForLaterConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->_customerHelperView = $customerHelperView;
        $this->saveForLaterSession = $saveForLaterSession;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->captchaHelper = $captchaHelper ?: ObjectManager::getInstance()->get(CaptchaHelper::class);
        $this->captchaStringResolver = $captchaStringResolver ?: ObjectManager::getInstance()->get(
            CaptchaStringResolver::class
        );
        $this->escaper = $escaper ?? ObjectManager::getInstance()->get(
            Escaper::class
        );
        parent::__construct($context);
    }

    /**
     * Share SaveForLater
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws NotFoundException|ValidateException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $captchaForName = 'share_saveforlater_form';
        /** @var CaptchaModel $captchaModel */
        $captchaModel = $this->captchaHelper->getCaptcha($captchaForName);

        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        $isCorrectCaptcha = $this->validateCaptcha($captchaModel, $captchaForName);

        $this->logCaptchaAttempt($captchaModel);

        if (!$isCorrectCaptcha) {
            $this->messageManager->addErrorMessage(__('Incorrect CAPTCHA'));
            $resultRedirect->setPath('*/*/share');
            return $resultRedirect;
        }

        $saveForLater = $this->saveForLaterProvider->getSaveForLater();
        if (!$saveForLater) {
            throw new NotFoundException(__('Page not found.'));
        }

        $sharingLimit = $this->saveForLaterConfig->getSharingEmailLimit();
        $textLimit = $this->saveForLaterConfig->getSharingTextLimit();
        $emailsLeft = $sharingLimit - $saveForLater->getShared();

        $emails = $this->getRequest()->getPost('emails');
        $emails = empty($emails) ? $emails : explode(',', $emails);

        $error = false;
        $message = (string)$this->getRequest()->getPost('message');
        if (strlen($message) > $textLimit) {
            $error = __('Message length must not exceed %1 symbols', $textLimit);
        } else {
            $message = nl2br((string) $this->escaper->escapeHtml($message));
            if (empty($emails)) {
                $error = __('Please enter an email address.');
            } else {
                if (count($emails) > $emailsLeft) {
                    $error = __('Maximum of %1 emails can be sent.', $emailsLeft);
                } else {
                    foreach ($emails as $index => $email) {
                        $email = $email !== null ? trim($email) : '';
                        if (!ValidatorChain::is($email, EmailAddress::class)) {
                            $error = __('Please enter a valid email address.');
                            break;
                        }
                        $emails[$index] = $email;
                    }
                }
            }
        }

        if ($error) {
            $this->messageManager->addErrorMessage($error);
            $this->saveForLaterSession->setSharingForm($this->getRequest()->getPostValue());
            $resultRedirect->setPath('*/*/share');
            return $resultRedirect;
        }
        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        $this->addLayoutHandles($resultLayout);
        $this->inlineTranslation->suspend();

        $sent = 0;

        try {
            $customer = $this->_customerSession->getCustomerDataObject();
            $customerName = $this->_customerHelperView->getCustomerName($customer);

            $message .= $this->getRssLink($saveForLater->getId(), $resultLayout);
            $emails = array_unique($emails);
            $sharingCode = $saveForLater->getSharingCode();

            try {
                foreach ($emails as $email) {
                    $transport = $this->_transportBuilder->setTemplateIdentifier(
                        $this->scopeConfig->getValue(
                            'saveforlater/email/email_template',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                        )
                    )->setTemplateOptions(
                        [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => $this->storeManager->getStore()->getStoreId(),
                        ]
                    )->setTemplateVars(
                        [
                            'customer' => $customer,
                            'customerName' => $customerName,
                            'salable' => $saveForLater->isSalable() ? 'yes' : '',
                            'items' => $this->getSaveForLaterItems($resultLayout),
                            'viewOnSiteLink' => $this->_url->getUrl('*/shared/index', ['code' => $sharingCode]),
                            'message' => $message,
                            'store' => $this->storeManager->getStore(),
                        ]
                    )->setFrom(
                        $this->scopeConfig->getValue(
                            'saveforlater/email/email_identity',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                        )
                    )->addTo(
                        $email
                    )->getTransport();

                    $transport->sendMessage();

                    $sent++;
                }
            } catch (\Exception $e) {
                $saveForLater->setShared($saveForLater->getShared() + $sent);
                $saveForLater->save();
                throw $e;
            }
            $saveForLater->setShared($saveForLater->getShared() + $sent);
            $saveForLater->save();

            $this->inlineTranslation->resume();

            $this->_eventManager->dispatch('saveforlater_share', ['saveforlater' => $saveForLater]);
            $this->messageManager->addSuccessMessage(__('Your save for later has been shared.'));
            $resultRedirect->setPath('*/*', ['saveforlater_id' => $saveForLater->getId()]);
            return $resultRedirect;
        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->saveForLaterSession->setSharingForm($this->getRequest()->getPostValue());
            $resultRedirect->setPath('*/*/share');
            return $resultRedirect;
        }
    }

    /**
     * Prepare to load additional email blocks
     *
     * Add 'saveforlater_email_rss' layout handle.
     * Add 'saveforlater_email_items' layout handle.
     *
     * @param \Magento\Framework\View\Result\Layout $resultLayout
     * @return void
     */
    protected function addLayoutHandles(ResultLayout $resultLayout)
    {
        if ($this->getRequest()->getParam('rss_url')) {
            $resultLayout->addHandle('saveforlater_email_rss');
        }
        $resultLayout->addHandle('saveforlater_email_items');
    }

    /**
     * Retrieve RSS link content (html)
     *
     * @param int $saveForLaterId
     * @param \Magento\Framework\View\Result\Layout $resultLayout
     */
    protected function getRssLink($saveForLaterId, ResultLayout $resultLayout)
    {
        if ($this->getRequest()->getParam('rss_url')) {
            return $resultLayout->getLayout()
                ->getBlock('saveforlater.email.rss')
                ->setSaveforlaterId($saveForLaterId)
                ->toHtml();
        }
    }

    /**
     * Retrieve SaveForLater items content (html)
     *
     * @param \Magento\Framework\View\Result\Layout $resultLayout
     * @return string
     */
    protected function getSaveForLaterItems(ResultLayout $resultLayout)
    {
        return $resultLayout->getLayout()
            ->getBlock('saveforlater.email.items')
            ->toHtml();
    }

    /**
     * Log customer action attempts
     *
     * @param CaptchaModel $captchaModel
     * @return void
     */
    private function logCaptchaAttempt(CaptchaModel $captchaModel): void
    {
        /** @var  Customer $customer */
        $customer = $this->_customerSession->getCustomer();
        $email = '';

        if ($customer->getId()) {
            $email = $customer->getEmail();
        }

        $captchaModel->logAttempt($email);
    }

    /**
     * Captcha validate logic
     *
     * @param CaptchaModel $captchaModel
     * @param string $captchaFormName
     * @return bool
     */
    private function validateCaptcha(CaptchaModel $captchaModel, string $captchaFormName) : bool
    {
        if ($captchaModel->isRequired()) {
            $word = $this->captchaStringResolver->resolve(
                $this->getRequest(),
                $captchaFormName
            );

            if (!$captchaModel->isCorrect($word)) {
                return false;
            }
        }

        return true;
    }
}
