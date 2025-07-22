<template>
    <AuthenticatedLayout :breadcrumbs="props?.breadcrumbs" :pageTitle="props?.pageTitle">
        <div class="card pt-5">
            <CompanyInformation :configuration="props?.configuration" />

            <div id="invoice-content">
                <h5 class="fs-3 text-center">{{ $t('report.label.fromDate', { date: formData.from_date }) }} - {{ $t('report.label.toDate', { date: formData.to_date, title: t('sidebarMenu.report.purchase') }) }}</h5>
            </div>

            <div class="mt-8 mb-0 ms-8" id="invoice-content">
                <h5 class="fs-3">{{ $t('buyer.label.totalPurchase') }}: <span class="fw-normal">{{ props.totalAmount }}</span></h5>
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
                            :placeholder="$t('report.placeholder.searchPurchases')"
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

                                    <!-- Start: Purchase type filter -->
                                    <div class="mb-10">
                                        <label class="form-label fs-6 fw-semibold">{{ $t('filterOptions.report.subTitle.label.purchaseType') }}:</label>
                                        <Multiselect
                                            :placeholder = "$t('filterOptions.report.subTitle.placeholder.purchaseType')"
                                            v-model="formData.purchase_type"
                                            :searchable="true"
                                            :options="props?.purchaseTypes"
                                            label="text"
                                            trackBy="value"
                                        />
                                    </div>
                                    <!-- End: Sale type filter -->

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
                        <button type="button" class="btn btn-primary my-1 me-12" @click="printInvoice">{{ $t('report.buttonValue.printInvoice') }}</button>
                        <!--end::Print-->
                    </div>
                    <!--end::Toolbar-->
                </div>
            </div>


            <div class="card-body pt-0" id="invoice-content">
                <Datatable
                    class="table"
                    @on-sort="sortData"
                    :data="computedPurchaseTable"
                    :header="tableHeader"
                    :enable-items-per-page-dropdown="false"
                    :items-per-page="Infinity"
                    :pagination-enabled="true"
                    :checkbox-enabled="false"
                >

                    <template v-slot:invoice_number="{ row: purchase }">
                        {{ purchase.invoice_number }}
                    </template>

                    <template v-slot:purchase_date="{ row: purchase }">
                        <span v-if="!purchase.is_total_row">{{ formatDate(purchase.purchase_date) }}</span>
                        <span v-else></span>
                    </template>

                    <template v-slot:supplier="{ row: purchase }">
                        {{ purchase.supplier }}
                    </template>

                    <template v-slot:purchase_type="{ row: purchase }">
                        <span v-if="!purchase.is_total_row">{{ purchase.purchase_type }}</span>
                        <strong v-else class="text-end">{{ $t('label.total') }}</strong>
                    </template>

                    <template v-slot:sub_total="{ row: purchase }">
                        <strong v-if="purchase.is_total_row">{{ purchase.sub_total }}</strong>
                        <span v-else>{{ purchase.sub_total }}</span>
                    </template>

                    <template v-slot:discount="{ row: purchase }">
                        <strong v-if="purchase.is_total_row">{{ purchase.discount }}</strong>
                        <span v-else>{{ purchase.discount }}</span>
                    </template>

                    <template v-slot:grand_total="{ row: purchase }">
                        <strong v-if="purchase.is_total_row">{{ purchase.grand_total }}</strong>
                        <span v-else>{{ purchase.grand_total }}</span>
                    </template>

                    <template v-slot:paid_amount="{ row: purchase }">
                        <strong v-if="purchase.is_total_row">{{ purchase.paid_amount }}</strong>
                        <span v-else>{{ purchase.paid_amount }}</span>
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
import KTIcon from "@/Core/helpers/kt-icon/KTIcon.vue";
import { MenuComponent } from "@/Assets/ts/components";
import i18n from '@/Core/plugins/i18n';
import dayjs from "dayjs";
import { Field } from "vee-validate";
import Multiselect from '@vueform/multiselect';
import { Inertia } from '@inertiajs/inertia';
import CompanyInformation from "@/Components/CompanyInformation.vue";

const { t } = i18n.global;

const props = defineProps({
    configuration: Object,
    purchases: Array as () => IPurchase[] | undefined,
    totalAmount: Number,
    purchaseTypes: Array,
    query: Object,
    breadcrumbs: Array as () => Breadcrumb[],
    pageTitle: String,
});

interface Breadcrumb {
    url: string;
    title: string;
}

interface IPurchase {
    id: number;
    supplier: string;
    invoice_number: string;
    purchase_date: string;
    purchase_type: string;
    sub_total: number;
    discount: number;
    grand_total: number;
    paid_amount: number;
    is_total_row?: boolean;
}

const tableHeader = ref([
    {
        columnName: t('purchase.label.purchaseDate'),
        columnLabel: "purchase_date",
        sortEnabled: true,
        columnWidth: 75,
    },
    {
        columnName: t('label.invoiceNumber'),
        columnLabel: "invoice_number",
        sortEnabled: true,
        columnWidth: 75,
    },
    {
        columnName: t('purchase.label.supplier'),
        columnLabel: "supplier",
        sortEnabled: true,
        columnWidth: 75,
    },
    {
        columnName: t('purchase.label.purchaseType'),
        columnLabel: "purchase_type",
        sortEnabled: true,
        columnWidth: 75,
    },
    {
        columnName: t('label.totalAmount'),
        columnLabel: "sub_total",
        sortEnabled: true,
        columnWidth: 75,
    },
    {
        columnName: t('sale.label.discountAmount'),
        columnLabel: "discount",
        sortEnabled: true,
        columnWidth: 75,
    },
    {
        columnName: t('sale.label.grandTotal'),
        columnLabel: "grand_total",
        sortEnabled: true,
        columnWidth: 75,
    },
    {
        columnName: t('label.paid'),
        columnLabel: "paid_amount",
        sortEnabled: true,
        columnWidth: 75,
    }
]);

const tableData = ref<IPurchase[]>([]);
const initData = ref<IPurchase[]>([]);

onMounted(() => {
    if(props.purchases){
        initData.value = props.purchases.map((purchase: any)=>({
            id: purchase.id,
            supplier: purchase.supplier?.name,
            invoice_number: purchase.invoice_number,
            purchase_date: purchase.purchase_date,
            purchase_type: purchase.purchase_type === 'larvae' ? 'পোনা' : 'অন্যান্য',
            sub_total: purchase.sub_total,
            discount: purchase.discount,
            grand_total: purchase.grand_total,
            paid_amount: purchase.paid_amount
        }));
        tableData.value = [...initData.value];
    }
});

//Calculate Summation
const getTotal = (field: keyof IPurchase) => {
  return tableData.value.reduce((sum, row) => sum + Number(row[field] || 0), 0);
};

const computedPurchaseTable = computed(() => {
    const rows = [...tableData.value];
    const subTotal = getTotal('sub_total');
    const discount = getTotal('discount');
    const grandTotal = getTotal('grand_total');
    const paidAmount = getTotal('paid_amount');

    rows.push({
        id: 0,
        supplier: '',
        invoice_number: '',
        purchase_date: '',
        purchase_type: '',
        is_total_row: true,
        sub_total: subTotal,
        discount: discount,
        grand_total: grandTotal,
        paid_amount: paidAmount
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
    to_date: to_date || today,
    purchase_type: ''
});

// Apply Filter Function
const applyFilter = () => {
    Inertia.replace(route('reports.purchase'), {
        preserveScroll: true,
        preserveState: true,
        data: {
            from_date: formData.from_date,
            to_date: formData.to_date,
            purchase_type: formData.purchase_type
        }
    });
};

const applyReset = () => {
    formData.from_date = today;
    formData.to_date = today;
    formData.purchase_type = '';
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
    console.log(tableData.value.length);
    document.title = `Purchase Report ${formData.from_date} to ${formData.to_date}`;
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
