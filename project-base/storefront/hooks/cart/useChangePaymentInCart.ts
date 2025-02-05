import { CartFragmentApi, useChangePaymentInCartMutationApi } from 'graphql/generated';
import { onGtmPaymentChangeEventHandler } from 'gtm/helpers/eventHandlers';
import { useGtmCartInfo } from 'gtm/helpers/gtm';
import { GtmMessageOriginType } from 'gtm/types/enums';
import { getUserFriendlyErrors } from 'helpers/errors/friendlyErrorMessageParser';
import { showErrorMessage } from 'helpers/toasts';
import { useLatest } from 'hooks/ui/useLatest';
import useTranslation from 'next-translate/useTranslation';
import { useCallback } from 'react';
import { usePersistStore } from 'store/usePersistStore';

export type ChangePaymentHandler = (
    newPaymentUuid: string | null,
    newGoPayBankSwift: string | null,
) => Promise<CartFragmentApi | undefined | null>;

export const useChangePaymentInCart = (): [ChangePaymentHandler, boolean] => {
    const [{ fetching }, changePaymentInCart] = useChangePaymentInCartMutationApi();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const { t } = useTranslation();
    const { gtmCartInfo } = useGtmCartInfo();

    const gtmCart = useLatest(gtmCartInfo);

    const changePaymentHandler = useCallback<ChangePaymentHandler>(
        async (newPaymentUuid, newGoPayBankSwift) => {
            const changePaymentResult = await changePaymentInCart(
                {
                    input: { paymentUuid: newPaymentUuid, paymentGoPayBankSwift: newGoPayBankSwift, cartUuid },
                },
                { additionalTypenames: ['dedup'] },
            );

            // EXTEND PAYMENT MODIFICATIONS HERE

            if (changePaymentResult.error !== undefined) {
                const { userError } = getUserFriendlyErrors(changePaymentResult.error, t);
                if (userError?.validation?.payment !== undefined) {
                    showErrorMessage(
                        userError.validation.payment.message,
                        GtmMessageOriginType.transport_and_payment_page,
                    );
                }
                if (userError?.validation?.goPaySwift !== undefined) {
                    showErrorMessage(
                        userError.validation.goPaySwift.message,
                        GtmMessageOriginType.transport_and_payment_page,
                    );
                }

                return null;
            }

            onGtmPaymentChangeEventHandler(
                gtmCart.current,
                changePaymentResult.data?.ChangePaymentInCart.payment ?? null,
            );

            return changePaymentResult.data?.ChangePaymentInCart;
        },
        [cartUuid, changePaymentInCart, gtmCart, t],
    );

    return [changePaymentHandler, fetching];
};
