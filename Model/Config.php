<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Model;

/**
 * Class Config for SaveForLater Configurations
 */
class Config
{
    const XML_PATH_SHARING_EMAIL_LIMIT = 'saveforlater/email/number_limit';

    const XML_PATH_SHARING_TEXT_LIMIT = 'saveforlater/email/text_limit';

    const SHARING_EMAIL_LIMIT = 10;

    const SHARING_TEXT_LIMIT = 255;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    private $catalogConfig;

    /**
     * @var \Magento\Catalog\Model\Attribute\Config
     */
    private $attributeConfig;

    /**
     * Number of emails allowed for sharing SaveForLater
     *
     * @var int
     */
    private $sharingEmailLimit;

    /**
     * @var int
     */
    private $sharignTextLimit;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param \Magento\Catalog\Model\Attribute\Config $attributeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Catalog\Model\Attribute\Config $attributeConfig
    ) {
        $emailLimitInConfig = (int)$scopeConfig->getValue(
            self::XML_PATH_SHARING_EMAIL_LIMIT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $textLimitInConfig = (int)$scopeConfig->getValue(
            self::XML_PATH_SHARING_TEXT_LIMIT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $this->sharingEmailLimit = $emailLimitInConfig ?: self::SHARING_EMAIL_LIMIT;
        $this->sharignTextLimit = $textLimitInConfig ?: self::SHARING_TEXT_LIMIT;
        $this->catalogConfig = $catalogConfig;
        $this->attributeConfig = $attributeConfig;
    }

    /**
     * Get product attributes that need in SaveForLater
     *
     * @return array
     */
    public function getProductAttributes()
    {
        $catalogAttributes = $this->catalogConfig->getProductAttributes();
        $saveForLaterAttributes = $this->attributeConfig->getAttributeNames('saveforlater_item');
        return array_merge($catalogAttributes, $saveForLaterAttributes);
    }

    /**
     * Retrieve number of emails allowed for sharing SaveForLater
     *
     * @return int
     */
    public function getSharingEmailLimit()
    {
        return $this->sharingEmailLimit;
    }

    /**
     * Retrieve maximum length of sharing email text
     *
     * @return int
     */
    public function getSharingTextLimit()
    {
        return $this->sharignTextLimit;
    }
}
