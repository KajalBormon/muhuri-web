<template>
    <AuthenticatedLayout :breadcrumbs="props?.breadcrumbs" :pageTitle="props?.pageTitle">
        <div class="card pt-5">
            <CompanyInformation :configuration="props?.configuration" />

            <div id="invoice-content">
                <h5 class="fs-3 text-center">{{ $t('report.label.fromDate', { date: formData.from_date }) }} - {{ $t('report.label.toDate', { date: formData.to_date, title: t('sidebarMenu.report.atAGlance') }) }}</h5>
            </div>

            <div class="card-header border-0">
                <div class="card-title">
                    <!--begin::Search-->
                    <div class="d-flex align-items-center position-relative my-1">
                        <KTIcon icon-name="magnifier" icon-class="fs-1 position-absolute ms-6" />
                        <input
                            type="text"
                            v-model="search"
                            @input="searchData()"
                            class="form-control form-control-solid w-250px ps-15"
                            :placeholder="$t('report.placeholder.searchSummaries')"
                        />
                    </div>
                    <!--end::Search-->
                </div>
                <div class="card-toolbar">
                    <!--begin::Toolbar-->
                    <div class="d-flex justify-content-end" data-kt-customer-table-toolbar="base">
                        <!-- Start: Filter -->
                        <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                            <i class="ki-duotone ki-filter fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>{{ $t('buttonValue.filter') }}
                        </button>
                        <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true">
                            <!--begin::Header-->
                            <div class="px-7 py-5">
                                <div class="fs-5 text-gray-900 fw-bold">{{ $t('filterOptions.title') }}</div>
                            </div>
                            <!--end::Header-->

                            <!--begin::Separator-->
                            <div class="separator border-gray-200"></div>
                            <!--end::Separator-->

                            <!--begin::Content-->
                            <div class="px-7 py-5" data-kt-user-table-filter="form">
                                <VForm @submit="submit()" class="form">
                                    <!-- Start: Date filter -->
                                    <div class="mb-10 row">
                                        <div class="col-md-6">
                                            <label class="form-label fs-6 fw-semibold">{{ $t('filterOptions.report.subTitle.label.fromDate') }}</label>
                                            <Field type="date" class="form-control form-control-lg form-control-solid" name="from_date" v-model="formData.from_date" />
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fs-6 fw-semibold">{{ $t('filterOptions.report.subTitle.label.toDate') }}</label>
                                            <Field type="date" class="form-control form-control-lg form-control-solid" name="to_date" v-model="formData.to_date" />
                                        </div>
                                    </div>
                                    <!-- End: Date filter -->

                                    <div class="d-flex justify-content-end">
                                        <button type="reset" class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6" data-kt-menu-dismiss="true" data-kt-user-table-filter="reset" @click="applyReset()">{{ $t('buttonValue.reset') }}</button>
                                        <button class="btn btn-primary fw-semibold px-6" data-kt-menu-dismiss="true" data-kt-user-table-filter="filter" @click="applyFilter()">{{ $t('buttonValue.apply') }}</button>
                                    </div>
                                </VForm>
                            </div>
                            <!--end::Content-->
                        </div>
                        <!--End: Filter-->

                        <!--begin::Print-->
                        <button type="button" class="btn btn-primary my-1 me-12" @click="printInvoice">{{ $t('sale.buttonValue.printInvoice') }}</button>
                        <!--end::Print-->
                    </div>
                    <!--end::Toolbar-->
                </div>
            </div>

            <div class="card-body pt-0 mt-0" id="invoice-content">
                <Datatable
                    @on-sort="sortData"
                    :data="computedSummaryTable"
                    :header="tableHeader"
                    :enable-items-per-page-dropdown="false"
                    :items-per-page="Infinity"
                    :pagination-enabled="true"
                    :checkbox-enabled="false"
                >
                    <template v-slot:summary_date="{ row: summary }">
                        <span v-if="!summary.is_total_row">{{ formatDate(summary.summary_date) }}</span>
                        <strong v-else class="text-end">{{ $t('label.total') }}</strong>
                    </template>

                    <template v-slot:total_purchase="{ row: summary }">
                        <strong v-if="summary.is_total_row">{{ summary.total_purchase }}</strong>
                        <span v-else>{{ summary.total_purchase }}</span>
                    </template>

                    <template v-slot:total_sale="{ row: summary }">
                        <strong v-if="summary.is_total_row">{{ summary.total_sale }}</strong>
                        <span v-else>{{ summary.total_sale }}</span>
                    </template>

                    <template v-slot:total_expense="{ row: summary }">
                        <strong v-if="summary.is_total_row">{{ summary.total_expense }}</strong>
                        <span v-else>{{ summary.total_expense }}</span>
                    </template>

                    <template v-slot:total_buyer_payment="{ row: summary }">
                        <strong v-if="summary.is_total_row">{{ summary.total_buyer_payment }}</strong>
                        <span v-else>{{ summary.total_buyer_payment }}</span>
                    </template>

                    <template v-slot:total_supplier_payment="{ row: summary }">
                        <strong v-if="summary.is_total_row">{{ summary.total_supplier_payment }}</strong>
                        <span v-else>{{ summary.total_supplier_payment }}</span>
                    </template>

                </Datatable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ref, defineProps, onMounted, computed } from "vue";
import Datatable from "@/Components/kt-datatable/KTDataTable.vue";
import type { Sort } from "@/Components/kt-datatable/table-partials/Models";
import arraySort from "array-sort";
import { useForm } from "@inertiajs/vue3";
import { Field } from "vee-validate";
import { Link } from "@inertiajs/vue3";
import KTIcon from "@/Core/helpers/kt-icon/KTIcon.vue";
import { MenuComponent } from "@/Assets/ts/components";
import { checkPermission } from "@/Core/helpers/Permission";
import i18n from '@/Core/plugins/i18n';
import dayjs from "dayjs";
import { Inertia } from '@inertiajs/inertia';
import CompanyInformation from "@/Components/CompanyInformation.vue";

const { t } = i18n.global;

const props = defineProps({
    configuration: Object,
    query: Object,
    summaries: Array as () => ISummary[] | undefined,
    breadcrumbs: Array as () => Breadcrumb[],
    pageTitle: String,
});

interface Breadcrumb {
    url: string;
    title: string;
}

interface ISummary {
    id: number;
    summary_date: string;
    total_purchase: number;
    total_sale: number;
    total_expense: number;
    total_buyer_payment: number;
    total_supplier_payment: number;
    is_total_row?: boolean;
}

const tableHeader = ref([
    {
        columnName: t('label.date'),
        columnLabel: "summary_date",
        sortEnabled: true,
        columnWidth: 100,
    },
    {
        columnName: t('buyer.label.totalPurchase'),
        columnLabel: "total_purchase",
        sortEnabled: true,
        columnWidth: 100,
    },
    {
        columnName: t('buyer.label.totalSale'),
        columnLabel: "total_sale",
        sortEnabled: true,
        columnWidth: 100,
    },
    {
        columnName: t('buyer.label.totalExpense'),
        columnLabel: "total_expense",
        sortEnabled: true,
        columnWidth: 100,
    },
    {
        columnName: t('buyer.label.totalBuyerPayment'),
        columnLabel: "total_buyer_payment",
        sortEnabled: true,
        columnWidth: 100,
    },
    {
        columnName: t('buyer.label.totalSupplierPayment'),
        columnLabel: "total_supplier_payment",
        sortEnabled: true,
        columnWidth: 100,
    }
]);

const tableData = ref<ISummary[]>([]);
const initData = ref<ISummary[]>([]);

onMounted(()=>{
    if(props.summaries){
        initData.value = props.summaries.map((summary: any)=>({
            id: summary.id,
            summary_date: summary.summary_date,
            total_purchase: summary.total_purchase,
            total_sale: summary.total_sale,
            total_expense: summary.total_expense,
            total_buyer_payment: summary.total_buyer_payment,
            total_supplier_payment: summary.total_supplier_payment
        }));
        tableData.value = [...initData.value];
    }
});

//Calculate Summation
const getTotal = (field: keyof ISummary) => {
  return tableData.value.reduce((sum, row) => sum + Number(row[field] || 0), 0);
};

const computedSummaryTable = computed(() => {
    const rows = [...tableData.value];
    const totalSale = getTotal('total_sale');
    const totalPurchase = getTotal('total_purchase');
    const totalExpense = getTotal('total_expense');
    const totalBuyerPayment = getTotal('total_buyer_payment');
    const totalSupplierPayment = getTotal('total_supplier_payment');

    rows.push({
        id: 0,
        summary_date: '',
        is_total_row: true,
        total_sale: totalSale,
        total_purchase: totalPurchase,
        total_expense: totalExpense,
        total_buyer_payment: totalBuyerPayment,
        total_supplier_payment: totalSupplierPayment,
    });

    return rows;
});

const formatDate = (dateString: string) => {
    return dayjs(dateString).format("DD MMMM YYYY");
};

const today = dayjs().format('YYYY-MM-DD');
const { from_date, to_date } = props.query || { from_date: '', to_date: '' };

const formData = useForm({
    from_date: from_date || today,
    to_date: to_date || today
});

// Apply Filter Function
const applyFilter = () => {
    Inertia.replace(route('reports.atAGlance'), {
        preserveScroll: true,
        preserveState: true,
        data: {
            from_date: formData.from_date,
            to_date: formData.to_date,
        }
    });
};

const applyReset = () => {
    formData.from_date = today;
    formData.to_date = today;
    applyFilter();
};

const submit = () => {
    applyFilter();
};

const search = ref<string>("");
const searchData = () => {
    tableData.value = [...initData.value];
    if (search.value !== "") {
        tableData.value = tableData.value.filter((item) =>
            searchingFunc(item, search.value),
        );
    }
    MenuComponent.reinitialization();
};

const searchingFunc = (obj: any, value: string): boolean => {
    for (let key in obj) {
        if (!Number.isInteger(obj[key]) && !(typeof obj[key] === "object")) {
            if (obj[key].includes(value)) {
                return true;
            }
        }
    }
    return false;
};

const sortData = (sort: Sort) => {
    const reverse: boolean = sort.order === "asc";
    if (sort.label) {
        arraySort(tableData.value, sort.label, {
            reverse,
        });
    }
};

const printInvoice = () => {
    document.title = `At a Glance Report ${formData.from_date} to ${formData.to_date}`;
    window.print();
};

</script>

<style>
    @media print {
        @page {
            margin-left: 0 !important;
            margin-right: 0 !important;
        }

        html,body * {
            /* overflow: hidden !important;  */
            visibility: hidden;
        }

        body {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        /* For second page don't show the heading again */
        thead {
            display: table-row-group !important;
        }

        .card-body, .app-container, .app-content, #kt_app_content {
            margin-top: -15px !important;
            padding-top: -15px !important;
        }

        .card-header{
            margin-top: -3rem;
        }

        #invoice-content, #invoice-content * {
            visibility: visible !important;
        }

        .table {
            page-break-inside: auto;
            border-collapse: collapse !important;
            width: 100% !important;
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        .table th,
        .table td {
            border: 0.2px solid #9291914f !important;
            padding: 2px 10px !important;
            text-align: center !important;
        }

        .table:not(.table-bordered) tfoot tr:last-child,
        .table:not(.table-bordered) tbody tr:last-child {
            border-bottom: 0.2px solid #9291914f !important;
        }
    }
</style>
