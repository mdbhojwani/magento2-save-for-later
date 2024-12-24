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

use Magento\Catalog\Model\Product\Exception as ProductException;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context as ActionContext;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Mdbhojwani\SaveForLater\Model\Item;
use Mdbhojwani\SaveForLater\Model\Item\OptionFactory;
use Mdbhojwani\SaveForLater\Model\ItemFactory;
use Mdbhojwani\SaveForLater\Model\ResourceModel\Item\Option\Collection as OptionCollection;

/**
 * SaveForLater Cart Controller
 */
class Cart extends Action implements HttpPostActionInterface
{
    /**
     * @var CustomerCart
     */
    protected $cart;

    /**
     * @var OptionFactory
     */
    protected $optionFactory;

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var CartHelper
     */
    protected $cartHelper;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @param ActionContext $context
     * @param CustomerCart $cart
     * @param OptionFactory $optionFactory
     * @param ItemFactory $itemFactory
     * @param CartHelper $cartHelper
     * @param Escaper $escaper
     */
    public function __construct(
        ActionContext $context,
        CustomerCart $cart,
        OptionFactory $optionFactory,
        ItemFactory $itemFactory,
        CartHelper $cartHelper,
        Escaper $escaper
    ) {
        $this->cart = $cart;
        $this->optionFactory = $optionFactory;
        $this->itemFactory = $itemFactory;
        $this->cartHelper = $cartHelper;
        $this->escaper = $escaper;
        parent::__construct($context);
    }

    /**
     * Add shared SaveForLater item to shopping cart
     *
     * If Product has required options - redirect
     * to product view page with message about needed defined required options
     *
     * @return Redirect
     */
    public function execute()
    {
        $itemId = (int)$this->getRequest()->getParam('item');

        /* @var $item Item */
        $item = $this->itemFactory->create()
            ->load($itemId);

        $redirectUrl = $this->_redirect->getRefererUrl();

        try {
            /** @var OptionCollection $options */
            $options = $this->optionFactory->create()
                ->getCollection()->addItemFilter([$itemId]);
            $item->setOptions($options->getOptionsByItem($itemId));
            $item->addToCart($this->cart);

            $this->cart->save();

            if (!$this->cart->getQuote()->getHasError()) {
                $message = __(
                    'You added %1 to your shopping cart.',
                    $this->escaper->escapeHtml($item->getProduct()->getName())
                );
                $this->messageManager->addSuccessMessage($message);
            }

            if ($this->cartHelper->getShouldRedirectToCart()) {
                $redirectUrl = $this->cartHelper->getCartUrl();
            }
        } catch (ProductException $e) {
            $this->messageManager->addErrorMessage(__('This product(s) is out of stock.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addNoticeMessage($e->getMessage());
            $redirectUrl = $item->getProductUrl();
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t add the item to the cart right now.'));
        }

        /** @var ResultRedirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($redirectUrl);

        return $resultRedirect;
    }
}
