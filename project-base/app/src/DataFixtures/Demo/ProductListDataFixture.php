<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListDataFactory;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum;

class ProductListDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const PRODUCT_LIST_WISHLIST_LOGGED_CUSTOMER_UUID = 'd76f456d-5ec2-41aa-99eb-cbc5b4b2a130';
    public const PRODUCT_LIST_COMPARISON_LOGGED_CUSTOMER_UUID = '63951da2-a886-4725-8ebb-1c12d3d3dd0c';
    public const PRODUCT_LIST_WISHLIST_NOT_LOGGED_CUSTOMER_UUID = '85817487-6c9b-4528-93cb-22fa0de9274d';
    public const PRODUCT_LIST_COMPARISON_NOT_LOGGED_CUSTOMER_UUID = 'dcc229ee-f93d-45bc-998b-63fb8e0ec3ec';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListDataFactory $productListDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade $productListFacade
     */
    public function __construct(
        private readonly ProductListDataFactory $productListDataFactory,
        private readonly ProductListFacade $productListFacade,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->getReference(CustomerUserDataFixture::USER_WITH_RESET_PASSWORD_HASH);
        /** @var \App\Model\Product\Product $productHelloKitty */
        $productHelloKitty = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        /** @var \App\Model\Product\Product $productIphone */
        $productIphone = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 5);
        /** @var \App\Model\Product\Product $productXperia */
        $productXperia = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 49);
        /** @var \App\Model\Product\Product $productToiletPaper */
        $productToiletPaper = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 33);
        /** @var \App\Model\Product\Product $productPhilipsTv */
        $productPhilipsTv = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 2);
        /** @var \App\Model\Product\Product $productLgTv */
        $productLgTv = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 3);

        $this->createProductList(ProductListTypeEnum::WISHLIST, $customerUser, self::PRODUCT_LIST_WISHLIST_LOGGED_CUSTOMER_UUID, [$productHelloKitty]);
        $this->createProductList(ProductListTypeEnum::COMPARISON, $customerUser, self::PRODUCT_LIST_COMPARISON_LOGGED_CUSTOMER_UUID, [$productIphone, $productXperia]);
        $this->createProductList(ProductListTypeEnum::WISHLIST, null, self::PRODUCT_LIST_WISHLIST_NOT_LOGGED_CUSTOMER_UUID, [$productToiletPaper]);
        $this->createProductList(ProductListTypeEnum::COMPARISON, null, self::PRODUCT_LIST_COMPARISON_NOT_LOGGED_CUSTOMER_UUID, [$productPhilipsTv, $productLgTv]);
    }

    /**
     * @param string $productListType
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     * @param string $uuid
     * @param \App\Model\Product\Product[] $products
     * @throws \Shopsys\FrameworkBundle\Model\Product\List\Exception\ProductAlreadyInListException
     */
    private function createProductList(
        string $productListType,
        ?CustomerUser $customerUser,
        string $uuid,
        array $products,
    ): void {
        $productListData = $this->productListDataFactory->create($productListType, $customerUser, $uuid);
        $productList = $this->productListFacade->create($productListData);

        foreach ($products as $product) {
            $this->productListFacade->addProductToList($productList, $product);
        }
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            ProductDataFixture::class,
            CustomerUserDataFixture::class,
        ];
    }
}
