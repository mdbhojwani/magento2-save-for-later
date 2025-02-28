<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Plugin\Helper\Product;

use Magento\Framework\App\Action\AbstractAction;
use Magento\Framework\DataObject;
use Magento\Framework\View\Result\Page;

/**
 * Parses the query string and pre-fills product quantity
 */
class View
{
    /**
     * Parses the query string and pre-fills product quantity
     *
     * @param \Magento\Catalog\Helper\Product\View $view
     * @param Page $resultPage
     * @param mixed $productId
     * @param AbstractAction $controller
     * @param mixed $params
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforePrepareAndRender(
        \Magento\Catalog\Helper\Product\View $view,
        Page $resultPage,
        $productId,
        AbstractAction $controller,
        $params = null
    ) {
        $qty = $controller->getRequest()->getParam('qty');
        if ($qty) {
            if (null === $params || !$params instanceof DataObject) {
                $params = new DataObject((array) $params);
            }
            if (!$params->getBuyRequest()) {
                $params->setBuyRequest(new DataObject([]));
            }
            $params->getBuyRequest()->setQty($qty);
        }

        return [$resultPage, $productId, $controller, $params];
    }
}
