import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useSettingsQueryApi } from 'graphql/generated';
import { formatPrice } from 'helpers/formaters/formatPrice';
import useTranslation from 'next-translate/useTranslation';

type FormatPriceFunctionType = (price: string | number, options?: { explicitZero?: boolean }) => string;

export const useFormatPrice = (): FormatPriceFunctionType => {
    const { t } = useTranslation();
    const [{ data }] = useSettingsQueryApi();
    const { defaultLocale = 'en' } = useDomainConfig();

    const { minimumFractionDigits = 0, defaultCurrencyCode = 'CZK' } = data?.settings?.pricing ?? {};
    const getPriceAsFloat = (price: string | number) => (typeof price === 'number' ? price : parseFloat(price));

    return (price, options) =>
        formatPrice(getPriceAsFloat(price), defaultCurrencyCode, t, defaultLocale, minimumFractionDigits, options);
};
