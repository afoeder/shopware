<?php declare(strict_types=1);

namespace Shopware\ShippingMethod\Searcher;

use Shopware\Search\SearchResultInterface;
use Shopware\ShippingMethod\Struct\ShippingMethodBasicCollection;

class ShippingMethodSearchResult extends ShippingMethodBasicCollection implements SearchResultInterface
{
    /**
     * @var int
     */
    protected $total = 0;

    public function getTotal(): int
    {
        return $this->total;
    }

    public function setTotal(int $total): void
    {
        $this->total = $total;
    }
}