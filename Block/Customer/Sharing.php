<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Block\Customer;

use Magento\Captcha\Block\Captcha;

/**
 * SaveForLater customer sharing block
 */
class Sharing extends \Magento\Framework\View\Element\Template
{
    /**
     * Entered Data cache
     *
     * @var array|null
     */
    protected $_enteredData = null;

    /**
     * SaveForLater configuration
     *
     * @var \Mdbhojwani\SaveForLater\Model\Config
     */
    protected $saveForLaterConfig;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $saveForLaterSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Mdbhojwani\SaveForLater\Model\Config $saveForLaterConfig
     * @param \Magento\Framework\Session\Generic $saveForLaterSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Mdbhojwani\SaveForLater\Model\Config $saveForLaterConfig,
        \Magento\Framework\Session\Generic $saveForLaterSession,
        array $data = []
    ) {
        $this->saveForLaterConfig = $saveForLaterConfig;
        $this->saveForLaterSession = $saveForLaterSession;
        parent::__construct($context, $data);
    }

    /**
     * Prepare Global Layout
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        if (!$this->getChildBlock('captcha')) {
            $this->addChild(
                'captcha',
                Captcha::class,
                [
                    'cacheable' => false,
                    'after' => '-',
                    'form_id' => 'share_saveforlater_form',
                    'image_width' => 230,
                    'image_height' => 230
                ]
            );
        }

        $this->pageConfig->getTitle()->set(__('Save For Later Sharing'));
    }

    /**
     * Retrieve Send Form Action URL
     *
     * @return string
     */
    public function getSendUrl()
    {
        return $this->getUrl('saveforlater/index/send');
    }

    /**
     * Retrieve Entered Data by key
     *
     * @param string $key
     * @return string|null
     */
    public function getEnteredData($key)
    {
        if ($this->_enteredData === null) {
            $this->_enteredData = $this->saveForLaterSession->getData('sharing_form', true);
        }

        if (!$this->_enteredData || !isset($this->_enteredData[$key])) {
            return null;
        } else {
            return $this->escapeHtml($this->_enteredData[$key]);
        }
    }

    /**
     * Retrieve back button url
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('saveforlater');
    }

    /**
     * Retrieve number of emails allowed for sharing
     *
     * @return int
     */
    public function getEmailSharingLimit()
    {
        return $this->saveForLaterConfig->getSharingEmailLimit();
    }

    /**
     * Retrieve maximum email length allowed for sharing
     *
     * @return int
     */
    public function getTextSharingLimit()
    {
        return $this->saveForLaterConfig->getSharingTextLimit();
    }
}
