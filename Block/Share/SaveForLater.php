<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Block\Share;

use Mdbhojwani\SaveForLater\Helper\Data as SaveForLaterHelper;

/**
 * SaveForLater block shared items
 */
class SaveForLater extends \Mdbhojwani\SaveForLater\Block\AbstractBlock
{
    /**
     * Customer instance
     *
     * @var \Magento\Customer\Api\Data\CustomerInterface
     */
    protected $_customer = null;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param SaveForLaterHelper $saveForLaterHelper
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        SaveForLaterHelper $saveForLaterHelper,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        array $data = []
    ) {
        $this->customerRepository = $customerRepository;
        parent::__construct(
            $context,
            $httpContext,
            $saveForLaterHelper,
            $data
        );
    }

    /**
     * Prepare global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set($this->getHeader());
        return $this;
    }

    /**
     * Retrieve Shared SaveForLater Customer instance
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getSaveForLaterCustomer()
    {
        if ($this->_customer === null) {
            $this->_customer = $this->customerRepository->getById($this->_getSaveForLater()->getCustomerId());
        }

        return $this->_customer;
    }

    /**
     * Retrieve Page Header
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeader()
    {
        return __("%1's Save For Later", $this->escapeHtml($this->getSaveForLaterCustomer()->getFirstname()));
    }
}
