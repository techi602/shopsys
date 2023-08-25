import {
    countryCZ,
    customer1,
    freePrice,
    orderNote,
    payment,
    products,
    zeroRate,
    standartRate,
    totalPrice,
    transport,
    url,
} from 'fixtures/demodata';
import { checkProductInCart } from 'support/cart';
import { checkProductAndGoToCartFromCartPopupWindow } from 'support/cartPopupWindow';
import { saveCookiesOptionsInCookiesBar } from 'support/cookies';
import { addProductToCartFromPromotedProductsOnHomepage } from 'support/homepage';
import {
    checkBasicInformationAndNoteInOrderDetail,
    checkBillingAdressInOrderDetail,
    checkDeliveryAdressInOrderDetail,
    checkOneItemInOrderDetail,
} from 'support/orderDetail';
import {
    continueToSecondStep,
    checkTransportPrice,
    chooseTransportPersonalCollectionAndStore,
    checkSelectedStoreInTransportList,
    choosePayment,
    checkOrderSummaryWithOneItem,
    continueToThirdStep,
} from 'support/orderSecondStep';
import {
    checkFinishOrderPageAsUnregistredCustomer,
    clickOnOrderDetailButtonOnThankYouPage,
} from 'support/orderThankYouPage';
import {
    fillEmailInThirdStep,
    fillCustomerInformationInThirdStep,
    fillBillingAdressInThirdStep,
    fillInNoteInThirdStep,
    clickOnSendOrderButton,
} from 'support/orderThirdStep';

it('Creating an order as unlogged user with one item, Personal collection and Cash', () => {
    cy.visit('/');
    saveCookiesOptionsInCookiesBar();
    addProductToCartFromPromotedProductsOnHomepage(products.helloKitty.catnum);
    checkProductAndGoToCartFromCartPopupWindow(products.helloKitty.namePrefixSuffix);
    checkProductInCart(products.helloKitty.catnum, products.helloKitty.namePrefixSuffix);
    cy.url().should('contain', url.cart);
    continueToSecondStep();

    cy.url().should('contain', url.order.secondStep);
    checkTransportPrice(2, freePrice);
    chooseTransportPersonalCollectionAndStore(transport.personalCollection.storeOstrava.name);
    checkSelectedStoreInTransportList(transport.personalCollection.storeOstrava.name);
    choosePayment(payment.cash);
    checkOrderSummaryWithOneItem(
        products.helloKitty.namePrefixSuffix,
        1,
        products.helloKitty.priceWithVat,
        transport.personalCollection.name,
        freePrice,
        payment.cash,
        freePrice,
        totalPrice.cart1,
    );
    continueToThirdStep();

    cy.url().should('contain', url.order.thirdStep);
    fillEmailInThirdStep(customer1.email);
    fillCustomerInformationInThirdStep(customer1.phone, customer1.firstName, customer1.lastName);
    fillBillingAdressInThirdStep(customer1.billingStreet, customer1.billingCity, customer1.billingPostCode);
    fillInNoteInThirdStep(orderNote);
    checkOrderSummaryWithOneItem(
        products.helloKitty.namePrefixSuffix,
        1,
        products.helloKitty.priceWithVat,
        transport.personalCollection.name,
        freePrice,
        payment.cash,
        freePrice,
        totalPrice.cart1,
    );
    clickOnSendOrderButton();

    checkFinishOrderPageAsUnregistredCustomer();
    clickOnOrderDetailButtonOnThankYouPage();

    checkBasicInformationAndNoteInOrderDetail(orderNote);
    checkBillingAdressInOrderDetail(
        customer1.firstName,
        customer1.lastName,
        customer1.email,
        customer1.phone,
        customer1.billingStreet,
        customer1.billingCity,
        customer1.billingPostCode,
        countryCZ,
    );
    checkDeliveryAdressInOrderDetail(
        customer1.firstName,
        customer1.lastName,
        customer1.phone,
        transport.personalCollection.storeOstrava.street,
        transport.personalCollection.storeOstrava.city,
        transport.personalCollection.storeOstrava.postcode,
        countryCZ,
    );
    checkOneItemInOrderDetail(
        0,
        products.helloKitty.namePrefixSuffix,
        products.helloKitty.priceWithVat,
        1,
        standartRate,
        products.helloKitty.priceWithoutVat,
        products.helloKitty.priceWithVat,
    );
    checkOneItemInOrderDetail(1, payment.cash, freePrice, 1, zeroRate, freePrice, freePrice);
    checkOneItemInOrderDetail(2, transport.personalCollection.name, freePrice, 1, standartRate, freePrice, freePrice);
});
