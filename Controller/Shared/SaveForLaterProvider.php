<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Controller\Shared;

use Mdbhojwani\SaveForLater\Controller\SaveForLaterProviderInterface;

/**
 * Class SaveForLaterProvider
 */
class SaveForLaterProvider implements SaveForLaterProviderInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Mdbhojwani\SaveForLater\Model\SaveForLaterFactory
     */
    protected $saveForLaterFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Mdbhojwani\SaveForLater\Model\SaveForLater
     */
    protected $saveForLater;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Mdbhojwani\SaveForLater\Model\SaveForLaterFactory $saveForLaterFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Mdbhojwani\SaveForLater\Model\SaveForLaterFactory $saveForLaterFactory,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->request = $request;
        $this->saveForLaterFactory = $saveForLatertFactory;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Retrieve current SaveForLater
     * @param string $saveForLaterId
     * @return \Mdbhojwani\SaveForLater\Model\SaveForLater
     */
    public function getSaveForLater($saveForLatertId = null)
    {
        if ($this->saveForLater) {
            return $this->saveForLater;
        }
        $code = (string)$this->request->getParam('code');
        if (empty($code)) {
            return false;
        }

        $saveForLater = $this->saveForLaterFactory->create()->loadByCode($code);
        if (!$saveForLater->getId()) {
            return false;
        }

        $this->checkoutSession->setSharedSaveForLater($code);
        $this->saveForLater = $saveForLater;
        return $saveForLater;
    }
}
