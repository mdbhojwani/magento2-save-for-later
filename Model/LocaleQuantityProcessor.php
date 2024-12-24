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
 * Class LocaleQuantityProcessor for SaveForLater
 */
class LocaleQuantityProcessor
{
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var \Magento\Framework\Filter\LocalizedToNormalized
     */
    protected $localFilter;

    /**
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\Filter\LocalizedToNormalized $localFilter
     */
    public function __construct(
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Filter\LocalizedToNormalized $localFilter
    ) {
        $this->localeResolver = $localeResolver;
        $this->localFilter = $localFilter;
    }

    /**
     * Process localized quantity to internal format
     *
     * @param float $qty
     * @return array|string
     */
    public function process($qty)
    {
        $this->localFilter->setOptions(['locale' => $this->localeResolver->getLocale()]);
        $qty = $this->localFilter->filter((string)$qty);
        if ($qty < 0) {
            $qty = null;
        }

        return $qty;
    }
}
