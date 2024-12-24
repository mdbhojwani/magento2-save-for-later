<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Block;

use Magento\Customer\Block\Account\SortLinkInterface;

/**
 * Class Link
 */
class Link extends \Magento\Framework\View\Element\Html\Link implements SortLinkInterface
{
    /**
     * Template name
     *
     * @var string
     */
    protected $_template = 'Mdbhojwani_SaveForLater::link.phtml';

    /**
     * @var \Mdbhojwani\SaveForLater\Helper\Data
     */
    protected $saveForLaterHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Mdbhojwani\SaveForLater\Helper\Data $saveForLaterHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Mdbhojwani\SaveForLater\Helper\Data $saveForLaterHelper,
        array $data = []
    ) {
        $this->saveForLaterHelper = $saveForLaterHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->saveForLaterHelper->isAllow()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->getUrl('saveforlater');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('My Save for Later');
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}
