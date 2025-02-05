<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Cart;

use App\DataFixtures\Demo\UnitDataFixture;
use App\Model\Cart\Cart;
use App\Model\Cart\Item\CartItem;
use App\Model\Product\Product;
use App\Model\Product\ProductData;
use App\Model\Product\ProductDataFactory;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\FrameworkBundle\Model\Product\Availability\Availability;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityData;
use Tests\App\Test\TransactionFunctionalTestCase;

class CartTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private ProductDataFactory $productDataFactory;

    /**
     * @inject
     */
    private VatFacade $vatFacade;

    public function testRemoveItem()
    {
        $customerUserIdentifier = new CustomerUserIdentifier('randomString');

        $availabilityData = new AvailabilityData();
        $availabilityData->dispatchTime = 0;
        $availability = new Availability($availabilityData);

        $productData = $this->productDataFactory->create();
        $productData->name = [];
        $productData->availability = $availability;
        $productData->catnum = '123';
        $productData->unit = $this->getReference(UnitDataFixture::UNIT_PIECES);
        $productData->manualInputPricesByPricingGroupId = [1 => Money::zero(), 2 => Money::zero()];
        $this->setVats($productData);
        $product1 = Product::create($productData);
        $productData2 = $productData;
        $productData2->catnum = '321';
        $product2 = Product::create($productData2);

        $cart = new Cart($customerUserIdentifier->getCartIdentifier(), null);

        $cartItem1 = new CartItem($cart, $product1, 1, Money::zero());
        $cart->addItem($cartItem1);
        $cartItem2 = new CartItem($cart, $product2, 3, Money::zero());
        $cart->addItem($cartItem2);

        $this->em->persist($cart);
        $this->em->persist($availability);
        $this->em->persist($product1);
        $this->em->persist($product2);
        $this->em->persist($cartItem1);
        $this->em->persist($cartItem2);
        $this->em->flush();

        $cart->removeItemById($cartItem1->getId());
        $this->em->remove($cartItem1);
        $this->em->flush();

        $this->assertSame(1, $cart->getItemsCount());
    }

    public function testCleanMakesCartEmpty()
    {
        $product = $this->createProduct();

        $customerUserIdentifier = new CustomerUserIdentifier('randomString');

        $cart = new Cart($customerUserIdentifier->getCartIdentifier(), null);

        $cartItem = new CartItem($cart, $product, 1, Money::zero());
        $cart->addItem($cartItem);

        $cart->clean();

        $this->assertTrue($cart->isEmpty());
    }

    /**
     * @return \App\Model\Product\Product
     */
    private function createProduct()
    {
        /** @var \App\Model\Product\ProductData $productData */
        $productData = $this->productDataFactory->create();
        $productData->name = ['cs' => 'Any name'];
        $productData->manualInputPricesByPricingGroupId = [1 => Money::zero(), 2 => Money::zero()];
        $this->setVats($productData);

        return Product::create($productData);
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    private function setVats(ProductData $productData): void
    {
        $productVatsIndexedByDomainId = [];

        foreach ($this->domain->getAllIds() as $domainId) {
            $productVatsIndexedByDomainId[$domainId] = $this->vatFacade->getDefaultVatForDomain($domainId);
        }
        $productData->vatsIndexedByDomainId = $productVatsIndexedByDomainId;
    }
}
