const em = (value) => value / 16 + 'em';

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: ['./pages/**/*.{js,ts,jsx,tsx}', './components/**/*.{js,ts,jsx,tsx}'],
    theme: {
        screens: {
            xs: em(320),
            sm: em(480),
            md: em(600),
            lg: em(769),
            vl: em(1024),
            xl: em(1240),
            xxl: em(1560),
        },
        colors: {
            text: '#25283D',
            textAccent: '#004EB6',
            textInverted: '#FFFFFF',
            textDisabled: '#727588',
            textSuccess: '#00CDBE',
            textError: '#EC5353',

            link: '#004EB6',
            linkDisabled: '#7AA1D5',
            linkHovered: '#003479',
            linkInverted: '#FFFFFF',
            linkInvertedDisabled: '#727588',
            linkInvertedHovered: '#FFF0C4',

            borderAccent: '#7892BC',
            borderAccentLess: '#E0E0E0',
            borderAccentSuccess: '#20D3C6',
            borderAccentError: '#EC5353',

            background: '#FFFFFF',
            backgroundMore: '#FAFAFA',
            backgroundMost: '#ECECEC',
            backgroundBrand: '#0F00A0',
            backgroundBrandLess: '#065FDB',
            backgroundAccent: '#009AFF',
            backgroundAccentLess: '#F4FAFF',
            backgroundAccentMore: '#008AE5',
            backgroundDark: '#25283D',
            backgroundError: '#EC5353',
            backgroundSuccess: '#20D3C6',
            backgroundWarning: '#FCBD46',

            price: '#004EB6',

            actionPrimaryBackground: '#00CDBE',
            actionPrimaryBorder: '#00CDBE',
            actionPrimaryText: '#FFFFFF',
            actionInvertedBackground: '#FFFFFF',
            actionInvertedBorder: '#004EB6',
            actionInvertedText: '#004EB6',
            actionPrimaryBackgroundDisabled: '#8AE4DD',
            actionPrimaryBorderDisabled: '#8AE4DD',
            actionPrimaryTextDisabled: '#FAFAFA',
            actionInvertedBackgroundDisabled: '#FAFAFA',
            actionInvertedBorderDisabled: '#B6C3D8',
            actionInvertedTextDisabled: '#B6C3D8',
            actionPrimaryBackgroundActive: '#01BEB0',
            actionPrimaryBorderActive: '#01BEB0',
            actionPrimaryTextActive: '#FFFFFF',
            actionInvertedBackgroundActive: '#FFFFFF',
            actionInvertedBorderActive: '#004EB6',
            actionInvertedTextActive: '#004EB6',
            actionPrimaryBackgroundHovered: '#01BEB0',
            actionPrimaryBorderHovered: '#01BEB0',
            actionPrimaryTextHovered: '#FFFFFF',
            actionInvertedBackgroundHovered: '#FFFFFF',
            actionInvertedBorderHovered: '#004EB6',
            actionInvertedTextHovered: '#004EB6',

            heartIconFull: '#EC5353',

            availabilityInStock: '#00CDBE',
            availabilityOutOfStock: '#EC5353',

            openingStatusOpen: '#00CDBE',
            openingStatusClosed: '#EC5353',
            openingStatusOpenToday: '#FCBD46',

            inputBorder: '#7892BC',
            inputPlaceholder: '#7892BC',
            inputText: '#25283D',
            inputTextInverted: '#FFFFFF',
            inputBackground: '#FFFFFF',
            inputBorderDisabled: '#AFBBCF',
            inputPlaceholderDisabled: '#AFBBCF',
            inputTextDisabled: '#727588',
            inputBackgroundDisabled: '#E3E3E3',
            inputBorderActive: '#3967B2',
            inputPlaceholderActive: '#3967B2',
            inputTextActive: '#25283D',
            inputBackgroundActive: '#FFFFFF',
            inputBorderHovered: '#5C81BE',
            inputPlaceholderHovered: '#5C81BE',
            inputTextHovered: '#25283D',
            inputBackgroundHovered: '#FFFFFF',
            inputError: '#EC5353',

            tableBackground: '#FFFFFF',
            tableBackgroundContrast: '#FAFAFA',
            tableBackgroundHeader: '#3967B2',
            tableText: '#25283D',
            tableTextHeader: '#FFFFFF',

            labelLinkBackground: '#7892BC',
            labelLinkText: '#FFFFFF',
            labelLinkBorder: '#7892BC',
            labelLinkBackgroundDisabled: '#AFBBCF',
            labelLinkTextDisabled: '#E3E3E3',
            labelLinkBorderDisabled: '#AFBBCF',
            labelLinkBackgroundActive: '#3967B2',
            labelLinkTextActive: '#FFFFFF',
            labelLinkBorderActive: '#3967B2',
            labelLinkBackgroundHovered: '#3967B2',
            labelLinkTextHovered: '#FFFFFF',
            labelLinkBorderHovered: '#3967B2',

            imageOverlay: 'rgba(201, 201, 201, 0.5)',
            overlay: 'rgba(37, 40, 61, 0.5)',
        },
        fontFamily: {
            default: ['var(--font-inter)'],
            secondary: ['var(--font-raleway)'],
        },
        zIndex: {
            hidden: -1000,
            above: 1,
            flag: 10,
            menu: 1010,
            aboveMenu: 1020,
            overlay: 1030,
            mobileMenu: 1040,
            aboveMobileMenu: 1050,
            cart: 6000,
            aboveOverlay: 10001,
            maximum: 10100,
        },
        extend: {
            lineHeight: {
                default: 1.3,
            },
            fontSize: {
                clamp: 'clamp(16px, 4vw, 22px)',
            },
        },
        plugins: [],
    },
};
