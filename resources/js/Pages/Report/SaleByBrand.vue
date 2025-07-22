<template>
    <AuthenticatedLayout :breadcrumbs="props?.breadcrumbs" :pageTitle="props?.pageTitle">
        <div class="card pt-5">
            <CompanyInformation :configuration="props?.configuration" />

            <div id="invoice-content">
                <h5 class="fs-3 text-center">{{ $t('report.label.fromDate', { date: formData.from_date }) }} - {{ $t('report.label.toDate', { date: formData.to_date, title: t('sidebarMenu.report.salesByBrand') }) }}</h5>
            </div>

            <div class="mt-8 mb-0 ms-8" id="invoice-content">
                <h5 class="fs-3">{{ $t('buyer.label.totalSale') }}: <span class="fw-normal">{{ props.totalAmount }}</span></h5>
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
                            :placeholder="$t('report.placeholder.searchSales')"
                        />
                    </div>
                    <!--end::Search-->
                </div>

                <div class="card-toolbar">
                    <!--begin::Toolbar-->
                    <div class="d-flex justify-content-end" data-kt-customer-table-toolbar="base">
                        <!-- Start: Filter -->
                        <button type="button" class="btn btn-light-primary me-3 my-1" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
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

                                    <!-- Start: Brand filter -->
                                    <div class="mb-10">
                                        <label class="form-label fs-6 fw-semibold">{{ $t('filterOptions.report.subTitle.label.brandName') }}:</label>
                                        <Multiselect
                                            :placeholder = "$t('filterOptions.report.subTitle.placeholder.selectBrand')"
                                            v-model="formData.brand_id"
                                            :searchable="true"
                                            :options="allBrands"
                                            label="text"
                                            trackBy="value"
                                        />
                                    </div>
                                    <!-- End: Brand filter -->

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


            <div class="card-body pt-0" id="invoice-content">
                <Datatable
                    class="table"
                    @on-sort="sortData"
                    :data="computedSaleByBrandTable"
                    :header="tableHeader"
                    :enable-items-per-page-dropdown="false"
                    :items-per-page="Infinity"
                    :pagination-enabled="true"
                    :checkbox-enabled="false"
                >

                    <template v-slot:sale_date="{ row: sale }">
                        {{ sale.sale_date }}
                    </template>

                     <template v-slot:item_name="{ row: sale }">
                        {{ sale.item_name }}
                    </template>

                    <template v-slot:brand_name="{ row: sale }">
                        <span v-if="!sale.is_total_row">{{ sale.brand_name }}</span>
                        <strong v-else class="text-end">{{ $t('label.total') }}</strong>
                    </template>

                    <template v-slot:total_box="{ row: sale }">
                        <strong v-if="sale.is_total_row">{{ sale.total_box }}</strong>
                        <span v-else>{{ sale.total_box }}</span>
                    </template>

                    <template v-slot:total_poly="{ row: sale }">
                        <strong v-if="sale.is_total_row">{{ sale.total_poly }}</strong>
                        <span v-else>{{ sale.total_poly }}</span>
                    </template>

                    <template v-slot:mir="{ row: sale }">
                        <span>{{ sale.mir }}</span>
                    </template>

                    <template v-slot:quantity="{ row: sale }">
                        <strong v-if="sale.is_total_row">{{ sale.quantity }}</strong>
                        <span v-else>{{ sale.quantity }}</span>
                    </template>

                    <template v-slot:total_price="{ row: sale }">
                        <strong v-if="sale.is_total_row">{{ sale.total_price }}</strong>
                        <span v-else>{{ sale.total_price }}</span>
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
    salesByBrand: Array as () => ISalesByBrand[] | undefined,
    totalAmount: Number,
    brands: Array,
    query: Object,
    breadcrumbs: Array as () => Breadcrumb[],
    pageTitle: String,
});

interface Breadcrumb {
    url: string;
    title: string;
}

interface ISalesByBrand {
    id: number;
    sale_date: string;
    item_name: string;
    brand_id: number;
    brand_name: string;
    total_box: number;
    total_poly: number;
    mir: number;
    quantity: number;
    total_price: number;
    is_total_row?: boolean;
}

const tableHeader = ref([
    {
        columnName: t('sale.label.saleDate'),
        columnLabel: "sale_date",
        sortEnabled: true,
        columnWidth: 75,
    },
    {
        columnName: t('label.itemName'),
        columnLabel: "item_name",
        sortEnabled: true,
        columnWidth: 75,
    },
    {
        columnName: t('label.brand'),
        columnLabel: "brand_name",
        sortEnabled: true,
        columnWidth: 75,
    },
    {
        columnName: t('label.box'),
        columnLabel: "total_box",
        sortEnabled: true,
        columnWidth: 75,
    },
    {
        columnName: t('label.poly'),
        columnLabel: "total_poly",
        sortEnabled: true,
        columnWidth: 75,
    },
    {
        columnName: t('label.mir'),
        columnLabel: "mir",
        sortEnabled: true,
        columnWidth: 75,
    },
    {
        columnName: t('sale.label.quantity'),
        columnLabel: "quantity",
        sortEnabled: true,
        columnWidth: 75,
    },
    {
        columnName: t('label.totalPrice'),
        columnLabel: "total_price",
        sortEnabled: true,
        columnWidth: 75,
    }
]);

const tableData = ref<ISalesByBrand[]>([]);
const initData = ref<ISalesByBrand[]>([]);

onMounted(() => {
    if(props.salesByBrand){
        initData.value = props.salesByBrand.map((sale: any)=>({
            id: sale.id,
            sale_date: sale.sale.sale_date,
            item_name: sale.item_name,
            brand_id: sale.brand_id,
            brand_name: sale.brand_name,
            total_box: sale.total_box,
            total_poly: sale.total_poly,
            quantity: sale.quantity,
            mir: sale.mir,
            total_price: sale.total_price,
        }));
        tableData.value = [...initData.value];
    }
});

//Calculate Summation
const getTotal = (field: keyof ISalesByBrand) => {
  return tableData.value.reduce((sum, row) => sum + Number(row[field] || 0), 0);
};

const computedSaleByBrandTable = computed(() => {
    const rows = [...tableData.value];
    const totalBox = getTotal('total_box');
    const totalPoly = getTotal('total_poly');
    // const totalMir = getTotal('mir');
    const totalPrice = getTotal('total_price');
    const totalQuantity = getTotal('quantity');

    rows.push({
        id: 0,
        sale_date: '',
        item_name: '',
        brand_id: 0,
        brand_name: '',
        mir: 0,
        is_total_row: true,
        total_box: totalBox,
        total_poly: totalPoly,
        quantity: totalQuantity,
        total_price: totalPrice
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
    brand_id: ''
});

// Apply Filter Function
const applyFilter = () => {
    Inertia.replace(route('reports.salesByBrand'), {
        preserveScroll: true,
        preserveState: true,
        data: {
            from_date: formData.from_date,
            to_date: formData.to_date,
            brand_id: formData.brand_id,
        }
    });
};

const applyReset = () => {
    formData.from_date = today;
    formData.to_date = today;
    formData.brand_id = '';
    applyFilter();
};

const submit = () => {
    applyFilter();
};

const allBrands = ref< Array <any>> ([]);
if (Array.isArray(props.brands) && props.brands.length > 0) {
    allBrands.value = props.brands.map((brand: any) => ({
        value: brand.id,
        text: brand.name,
    }));
}

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
    document.title = `Sales By Brand Report ${formData.from_date} to ${formData.to_date}`;
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
