<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Shopsys\FrameworkBundle\Controller\Admin\ProductController as BaseProductController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @property \App\Model\Product\ProductFacade $productFacade
 * @property \App\Model\Product\ProductDataFactory $productDataFactory
 * @property \App\Component\Setting\Setting $setting
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 * @method setSellingToUntilEndOfDay(\App\Model\Product\ProductData|null $productData)
 * @method __construct(\Shopsys\FrameworkBundle\Model\Product\MassAction\ProductMassActionFacade $productMassActionFacade, \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory, \App\Model\Product\ProductFacade $productFacade, \App\Model\Product\ProductDataFactory $productDataFactory, \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider, \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade $administratorGridFacade, \Shopsys\FrameworkBundle\Model\Product\Listing\ProductListAdminFacade $productListAdminFacade, \Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchProductFacade $advancedSearchProductFacade, \Shopsys\FrameworkBundle\Model\Product\ProductVariantFacade $productVariantFacade, \Shopsys\FrameworkBundle\Twig\ProductExtension $productExtension, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \App\Model\Product\Unit\UnitFacade $unitFacade, \App\Component\Setting\Setting $setting, \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade $availabilityFacade)
 * @property \Shopsys\FrameworkBundle\Model\Product\ProductVariantFacade $productVariantFacade
 * @property \App\Model\Product\Unit\UnitFacade $unitFacade
 */
class ProductController extends BaseProductController
{
    /**
     * @Route("/product/edit/catnum-exists")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function catnumExistsAction(Request $request): Response
    {
        $catnum = $request->get('catnum');
        $currentProductCatnum = $request->get('currentProductCatnum');

        if ($catnum === null || $catnum === $currentProductCatnum) {
            return new JsonResponse(false);
        }

        $productByCatnum = $this->productFacade->findByCatnum($catnum);

        return new JsonResponse($productByCatnum !== null);
    }

    /**
     * This route is used by GrapesJS to load Names of products
     *
     * @Route("/product/names-by-catnums", methods={"post"}, condition="request.isXmlHttpRequest()")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function productNamesByCatnumsAction(Request $request): JsonResponse
    {
        $catnums = $request->get('catnums');

        $response = [];
        $products = $this->productFacade->findAllByCatnums($catnums);

        foreach ($products as $product) {
            $response[$product->getCatnum()] = $product->getName();
        }

        return new JsonResponse($response);
    }
}
