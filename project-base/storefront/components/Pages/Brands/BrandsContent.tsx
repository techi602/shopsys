import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { SkeletonPageBrandsOverview } from 'components/Blocks/Skeleton/SkeletonPageBrandsOverview';
import { Webline } from 'components/Layout/Webline/Webline';
import { useBrandsQueryApi } from 'graphql/generated';

export const BrandsContent: FC = () => {
    const [{ data: brandsData, fetching }] = useBrandsQueryApi();

    if (fetching) {
        return <SkeletonPageBrandsOverview />;
    }

    if (!brandsData) {
        return null;
    }

    return (
        <Webline>
            <SimpleNavigation isWithoutSlider listedItems={brandsData.brands} />
        </Webline>
    );
};
