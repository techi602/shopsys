import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { useCategoryDetailData, useHandleDefaultFiltersUpdate } from 'components/Pages/CategoryDetail/utils';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import {
    TypeCategoryDetailQuery,
    TypeCategoryDetailQueryVariables,
    CategoryDetailQueryDocument,
} from 'graphql/requests/categories/queries/CategoryDetailQuery.generated';
import { CategoryProductsQueryDocument } from 'graphql/requests/products/queries/CategoryProductsQuery.generated';
import { NextPage } from 'next';
import dynamic from 'next/dynamic';
import { Suspense } from 'react';
import { createClient } from 'urql/createClient';
import { handleServerSideErrorResponseForFriendlyUrls } from 'utils/errors/handleServerSideErrorResponseForFriendlyUrls';
import { getMappedProductFilter } from 'utils/filterOptions/getMappedProductFilter';
import { isRedirectedFromSsr } from 'utils/isRedirectedFromSsr';
import { getRedirectWithOffsetPage } from 'utils/loadMore/getRedirectWithOffsetPage';
import { getNumberFromUrlQuery } from 'utils/parsing/getNumberFromUrlQuery';
import { getProductListSortFromUrlQuery } from 'utils/parsing/getProductListSortFromUrlQuery';
import { getSlugFromServerSideUrl } from 'utils/parsing/getSlugFromServerSideUrl';
import {
    PAGE_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
    FILTER_QUERY_PARAMETER_NAME,
    LOAD_MORE_QUERY_PARAMETER_NAME,
} from 'utils/queryParamNames';
import { useCurrentFilterQuery } from 'utils/queryParams/useCurrentFilterQuery';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { ServerSidePropsType, initServerSideProps } from 'utils/serverSide/initServerSideProps';

const CategoryDetailWrapper = dynamic(() =>
    import('components/Pages/CategoryDetail/CategoryDetailWrapper').then(
        (component) => component.CategoryDetailWrapper,
    ),
);

const CategoryDetailPage: NextPage<ServerSidePropsType> = () => {
    const currentFilter = useCurrentFilterQuery();
    const { categoryData, isFetchingVisible } = useCategoryDetailData(currentFilter);

    useHandleDefaultFiltersUpdate(categoryData?.products);

    return (
        <Suspense>
            <CategoryDetailWrapper
                categoryData={categoryData}
                currentFilter={currentFilter}
                isFetchingVisible={isFetchingVisible}
            />
        </Suspense>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, ssrExchange, t }) =>
        async (context) => {
            const page = getNumberFromUrlQuery(context.query[PAGE_QUERY_PARAMETER_NAME], 1);
            const loadMore = getNumberFromUrlQuery(context.query[LOAD_MORE_QUERY_PARAMETER_NAME], 0);
            const urlSlug = getSlugFromServerSideUrl(context.req.url ?? '');
            const redirect = getRedirectWithOffsetPage(page, loadMore, urlSlug, context.query);

            if (redirect) {
                return redirect;
            }

            const client = await createClient({
                publicGraphqlEndpoint: domainConfig.publicGraphqlEndpoint,
                ssrExchange,
                redisClient,
                context,
                t,
            });

            if (isRedirectedFromSsr(context.req.headers)) {
                const filter = getMappedProductFilter(context.query[FILTER_QUERY_PARAMETER_NAME]);
                const orderingMode = getProductListSortFromUrlQuery(context.query[SORT_QUERY_PARAMETER_NAME]);
                const categoryDetailResponsePromise = client!
                    .query<TypeCategoryDetailQuery, TypeCategoryDetailQueryVariables>(CategoryDetailQueryDocument, {
                        urlSlug,
                        filter,
                        orderingMode,
                    })
                    .toPromise();

                const categoryProductsResponsePromise = client!
                    .query(CategoryProductsQueryDocument, {
                        endCursor: getEndCursor(page),
                        orderingMode,
                        filter,
                        urlSlug,
                        pageSize: DEFAULT_PAGE_SIZE * (loadMore + 1),
                    })
                    .toPromise();

                const [categoryDetailResponse] = await Promise.all([
                    categoryDetailResponsePromise,
                    categoryProductsResponsePromise,
                ]);

                const serverSideErrorResponse = handleServerSideErrorResponseForFriendlyUrls(
                    categoryDetailResponse.error?.graphQLErrors,
                    categoryDetailResponse.data?.category,
                    context.res,
                );

                if (serverSideErrorResponse) {
                    return serverSideErrorResponse;
                }
            }

            const initServerSideData = await initServerSideProps({
                domainConfig,
                context,
                client,
                ssrExchange,
            });

            return initServerSideData;
        },
);

export default CategoryDetailPage;
