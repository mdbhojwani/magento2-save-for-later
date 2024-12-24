<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Block\Rss;

use Magento\Framework\App\Rss\UrlBuilderInterface;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mdbhojwani\SaveForLater\Helper\Data;

/**
 * SaveForLater block customer items
 */
class Link extends Template
{
    /**
     * @var Data
     */
    protected Data $saveForLaterHelper;

    /**
     * @var UrlBuilderInterface
     */
    protected UrlBuilderInterface $rssUrlBuilder;

    /**
     * @var EncoderInterface
     */
    protected EncoderInterface $urlEncoder;

    /**
     * @param Context $context
     * @param Data $saveForLaterHelper
     * @param UrlBuilderInterface $rssUrlBuilder
     * @param EncoderInterface $urlEncoder
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $saveForLaterHelper,
        UrlBuilderInterface $rssUrlBuilder,
        EncoderInterface $urlEncoder,
        array $data = []
    ) {
        $data['saveForLaterHelper'] = $saveForLaterHelper;
        parent::__construct($context, $data);
        $this->saveForLaterHelper = $saveForLaterHelper;
        $this->rssUrlBuilder = $rssUrlBuilder;
        $this->urlEncoder = $urlEncoder;
    }

    /**
     * Return link.
     *
     * @return string
     */
    public function getLink()
    {
        return $this->rssUrlBuilder->getUrl($this->getLinkParams());
    }

    /**
     * Check whether status notification is allowed
     *
     * @return bool
     */
    public function isRssAllowed()
    {
        return $this->_scopeConfig->isSetFlag(
            'rss/saveforlater/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return link params.
     *
     * @return array
     */
    protected function getLinkParams()
    {
        $params = [];
        $saveForLaterId = $this->saveForLaterHelper->getSaveForLater()->getId();
        $customer = $this->saveForLaterHelper->getCustomer();
        if ($customer) {
            $key = $customer->getId() . ',' . $customer->getEmail();
            $params = [
                'type' => 'saveforlater',
                'data' => $this->urlEncoder->encode($key),
                '_secure' => false
            ];
        }
        if ($saveForLaterId) {
            $params['saveforlater_id'] = $saveForLaterId;
        }
        return $params;
    }
}
