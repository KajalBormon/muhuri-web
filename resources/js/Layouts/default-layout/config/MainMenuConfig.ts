import type {MenuItem} from "@/Layouts/default-layout/config/types";
import i18n from '@/Core/plugins/i18n';

const {t} = i18n.global;

const sidebarMenuConfig: Array<MenuItem> = [
    {
        pages: [
            {
                heading: t('sidebarMenu.dashboard'),
                route: "/dashboard",
                keenthemesIcon: "element-11",
                bootstrapIcon: "bi-app-indicator",
            }
        ],
    },

    {
        heading: t('sidebarMenu.section.admin'),
        route: "/configurations",
        headingRoutes: ["/configurations", "/roles", "/users"],
        headingPermissions: ["can-view-sale", "can-view-buyer", "can-view-supplier", "can-view-purchase", "can-view-expense"],
        pages: [
            {
                sectionTitle: t('sidebarMenu.orderManagement.menu'),
                route: "/sales",
                keenthemesIcon: "lots-shopping",
                bootstrapIcon: "bi-archive",
                routeArray: ["/buyers","/sales"],
                routePermissions: ["can-view-sale", "can-view-buyer"],
                sub: [
                    {
                        heading: t('sidebarMenu.buyerManagement.menu'),
                        route: "/buyers",
                        permission: "can-view-buyer",
                    },
                    {
                        heading: t('sidebarMenu.orderManagement.subMenu.sales'),
                        route: "/sales",
                        permission: "can-view-sale",
                    },
                ],
            },
            {
                sectionTitle: t('sidebarMenu.purchaseManagement.menu'),
                route: "/purchases",
                keenthemesIcon: "purchase",
                bootstrapIcon: "bi-archive",
                routeArray: ["/suppliers", "/purchases"],
                routePermissions: ["can-view-supplier", "can-view-purchase"],
                sub: [
                    {
                        heading: t('sidebarMenu.purchaseManagement.subMenu.suppliers'),
                        route: "/suppliers",
                        permission: "can-view-supplier",
                    },
                    {
                        heading: t('sidebarMenu.purchaseManagement.subMenu.purchases'),
                        route: "/purchases",
                        permission: "can-view-purchase",
                    },
                ],
            },
            {
                heading: t('sidebarMenu.expenseManagement.menu'),
                route: "/expenses",
                routePermissions: ["can-view-expense"],
                keenthemesIcon: "tag",
                bootstrapIcon: "bi-archive",
            },
        ],
    },
    {
        heading: t('sidebarMenu.section.hr'),
        route: "/crafted",
        headingRoutes: ["/employees", "/job-positions", "/subscriptions"],
        headingPermissions: ["can-view-employee", "can-view-job-position", "can-view-subscription", "can-view-departure-reason"],
        pages: [
            {
                sectionTitle: t('sidebarMenu.employee.menu'),
                route: "/employees",
                keenthemesIcon: "user-square",
                bootstrapIcon: "bi-archive",
                routeArray: ["/employees", "/job-positions", "/departure-reasons"],
                routePermissions: ["can-view-job-position", "can-view-employee", "can-view-departure-reason"],
                sub: [
                    {
                        heading: t('sidebarMenu.employee.submenu.jobPosition'),
                        route: "/job-positions",
                        permission: "can-view-job-position",
                    },
                    {
                        heading: t('sidebarMenu.employee.submenu.departureReason'),
                        route: '/departure-reasons',
                        permission: "can-view-departure-reason"
                    },
                    {
                        heading: t('sidebarMenu.employee.submenu.employee'),
                        route: "/employees",
                        permission: "can-view-employee",
                    },

                ],
            },
        ]
    },

    {
        heading: t('sidebarMenu.section.report'),
        route: "/reports/at-a-glance",
        headingRoutes: ["/reports/at-a-glance"],
        headingPermissions: ["can-view-atAGlance"],
        pages: [
            {
                heading: t('buyer.label.atAGlance'),
                route: "/reports/at-a-glance",
                routePermissions: ["can-view-atAGlance"],
                keenthemesIcon: "eye",
                bootstrapIcon: "bi-archive",
            },
            {
                heading: t('sidebarMenu.report.buyerDue'),
                route: "/reports/buyer-due",
                routePermissions: ["can-view-buyer-due"],
                keenthemesIcon: "two-credit-cart",
                bootstrapIcon: "bi-archive",
            },
            {
                heading: t('sidebarMenu.report.buyerPayment'),
                route: "/reports/buyer-payment",
                routePermissions: ["can-view-buyer-payment-report"],
                keenthemesIcon: "tag",
                bootstrapIcon: "bi-archive",
            },
            {
                heading: t('sidebarMenu.report.supplierDue'),
                route: "/reports/supplier-due",
                routePermissions: ["can-view-supplier-due"],
                keenthemesIcon: "two-credit-cart",
                bootstrapIcon: "bi-archive",
            },
            {
                heading: t('sidebarMenu.report.supplierPayment'),
                route: "/reports/supplier-payment",
                routePermissions: ["can-view-supplier-payment-report"],
                keenthemesIcon: "tag",
                bootstrapIcon: "bi-archive",
            },
            {
                heading: t('sidebarMenu.report.sale'),
                route: "/reports/sale",
                routePermissions: ["can-view-sale-report"],
                keenthemesIcon: "delivery-3",
                bootstrapIcon: "bi-archive",
            },
            {
                heading: t('sidebarMenu.report.salesByBrand'),
                route: '/reports/brand-with-sale',
                routePermissions: ["can-view-sale-by-brand-report"],
                keenthemesIcon: "chart-line-star",
                bootstrapIcon: "bi-archive",
            },
            {
                heading: t('sidebarMenu.report.purchase'),
                route: "/reports/purchase",
                routePermissions: ["can-view-purchase-report"],
                keenthemesIcon: "credit-cart",
                bootstrapIcon: "bi-archive",
            },
            {
                heading: t('sidebarMenu.report.expense'),
                route: "/reports/expense",
                routePermissions: ["can-view-expense-report"],
                keenthemesIcon: "wallet",
                bootstrapIcon: "bi-archive",
            }
        ]
    },
    {
        heading: t('sidebarMenu.section.admin'),
        route: "/configurations",
        headingRoutes: ["/configurations", "/roles", "/users"],
        headingPermissions: ["can-view-configuration", "can-view-role", "can-view-user", "can-view-item", "can-view-fiscal-year", "can-view-sms-dashboard", "can-view-brand", "can-view-subscription"],
        pages: [
            {
                heading: t('sidebarMenu.items.menu'),
                route: "/items",
                routePermissions: ["can-view-item"],
                keenthemesIcon: "abstract-24",
                bootstrapIcon: "bi-archive",
            },
            {
                heading: t('sidebarMenu.brand.menu'),
                route: "/brands",
                routePermissions: ["can-view-brand"],
                keenthemesIcon: "text-bold",
                bootstrapIcon: "bi-archive",
            },
            {
                heading: t('sidebarMenu.fiscalYear.menu'),
                route: "/fiscal-years",
                routePermissions: ["can-view-fiscal-year"],
                keenthemesIcon: "calendar",
                bootstrapIcon: "bi-archive",
            },
            {
                heading: t('sidebarMenu.smsManagement.menu'),
                route: "/sms",
                routePermissions: ["can-view-sms-dashboard"],
                keenthemesIcon: "messages",
                bootstrapIcon: "bi-archive",
            },
            {
                sectionTitle: t('sidebarMenu.userManagement.menu'),
                route: "/users",
                keenthemesIcon: "profile-user",
                bootstrapIcon: "bi-archive",
                routeArray: ["/roles", "/users"],
                routePermissions: ["can-view-role", "can-view-user"],
                sub: [
                    {
                        heading: t('sidebarMenu.userManagement.submenu.roles'),
                        route: "/roles",
                        permission: "can-view-role",
                    },
                    {
                        heading: t('sidebarMenu.userManagement.submenu.users'),
                        route: "/users",
                        permission: "can-view-user",
                    },
                ],
            },
            {
                heading: t('sidebarMenu.configuration.menu'),
                route: "/configurations",
                routePermissions: ["can-view-configuration"],
                keenthemesIcon: "setting-2",
                bootstrapIcon: "bi-archive",
            },
            {
                heading: t('sidebarMenu.subscription.menu'),
                route: "/subscriptions",
                routePermissions: ["can-view-subscription"],
                keenthemesIcon: "basket-ok",
                bootstrapIcon: "bi-archive",
            },
        ]
    }
];

export default sidebarMenuConfig;
