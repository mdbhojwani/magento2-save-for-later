<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Block\Cart\Item\Renderer\Actions;

use Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic;
use Magento\Framework\View\Element\Template;
use Mdbhojwani\SaveForLater\Helper\Data;

/**
 * Class SaveForLater
 */
class SaveForLater extends Generic
{
    /**
     * @var Data
     */
    protected $saveForLaterHelper;

    /**
     * @param Template\Context $context
     * @param Data $saveForLaterHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Data $saveForLaterHelper,
        array $data = []
    ) {
        $this->saveForLaterHelper = $saveForLaterHelper;
        parent::__construct($context, $data);
    }

    /**
     * Check whether "save for later" button is allowed in cart
     *
     * @return bool
     */
    public function isAllowInCart()
    {
        return $this->saveForLaterHelper->isAllowInCart();
    }

    /**
     * Get JSON POST params for moving from cart
     *
     * @return string
     */
    public function getMoveFromCartParams()
    {
        return $this->saveForLaterHelper->getMoveFromCartParams($this->getItem()->getId());
    }
}
