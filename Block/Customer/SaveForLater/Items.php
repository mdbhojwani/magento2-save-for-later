<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Block\Customer\SaveForLater;

/**
 * SaveForLater block customer items
 */
class Items extends \Magento\Framework\View\Element\Template
{
    /**
     * Retrieve table column object list
     *
     * @return \Mdbhojwani\SaveForLater\Block\Customer\SaveForLater\Item\Column[]
     */
    public function getColumns()
    {
        $columns = [];
        foreach ($this->getLayout()->getChildBlocks($this->getNameInLayout()) as $child) {
            if ($child instanceof \Mdbhojwani\SaveForLater\Block\Customer\SaveForLater\Item\Column && $child->isEnabled()) {
                $columns[] = $child;
            }
        }
        return $columns;
    }
}
