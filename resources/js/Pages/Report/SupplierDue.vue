<template>
    <AuthenticatedLayout :breadcrumbs="props?.breadcrumbs" :pageTitle="props?.pageTitle">
        <div class="card pt-5">
            <CompanyInformation :configuration="props?.configuration" />

            <div class="mt-8 mb-0 ms-8" id="invoice-content">
                <h5 class="fs-3">{{ $t('label.totalDue') }}: <span class="fw-normal">{{ props.totalDue }}</span></h5>
            </div>

            <div class="card-header border-0">
                <div class="card-title">
                    <div class="d-flex align-items-center position-relative my-1">
                        <KTIcon icon-name="magnifier" icon-class="fs-1 position-absolute ms-6" />
                        <input
                            type="text"
                            v-model="search"
                            @input="searchData()"
                            class="form-control form-control-solid w-250px ps-15"
                            :placeholder="$t('supplier.placeholder.searchSuppliers')"
                        />
                    </div>
                </div>
                <div class="card-toolbar">
                    <!--begin::Toolbar-->
                    <div class="d-flex justify-content-end" data-kt-customer-table-toolbar="base" >
                        <!--begin::Add Fiscal Year-->
                        <button v-if="checkPermission ('can-create-supplier')"
                            class="btn btn-primary me-3"
                            @click.prevent="sendReminder"
                        >
                            <i class="fa-solid fa-bell"></i> {{ $t("supplier.header.sendDueReminder") }}
                        </button>
                        <!--end::Add Fiscal Year-->

                        <!--begin::Print-->
                        <button type="button" class="btn btn-primary" @click="printInvoice">{{ $t('report.buttonValue.printInvoice') }}</button>
                        <!--end::Print-->
                    </div>
                    <!--end::Toolbar-->
                </div>
            </div>
            <div class="card-body pt-0" id="invoice-content">
                <Datatable
                    @on-sort="sortData"
                    :data="computedSupplierDueTable"
                    :header="tableHeader"
                    :enable-items-per-page-dropdown="false"
                    :items-per-page="Infinity"
                    :pagination-enabled="true"
                    :checkbox-enabled="false"
                >
                    <template v-slot:name="{ row: supplierDue }">
                        <Link :href="route('suppliers.show', supplierDue.id)" class="text-gray-800 text-hover-primary">
                            {{ supplierDue.name }}
                        </Link>
                    </template>

                    <template v-slot:mobile_number="{ row: supplierDue }">
                        <span v-if="!supplierDue.is_total_row">{{ supplierDue.mobile_number }}</span>
                        <strong v-else class="text-end">{{ $t('label.total') }}</strong>
                    </template>

                    <template v-slot:total_transaction="{ row: supplierDue }">
                        <strong v-if="supplierDue.is_total_row">{{ supplierDue.total_transaction }}</strong>
                        <span v-else>{{ supplierDue.total_transaction }}</span>
                    </template>

                    <template v-slot:total_payment="{ row: supplierDue }">
                        <strong v-if="supplierDue.is_total_row">{{ supplierDue.total_payment }}</strong>
                        <span v-else>{{ supplierDue.total_payment }}</span>
                    </template>

                    <template v-slot:total_due="{ row: supplierDue }">
                        <strong v-if="supplierDue.is_total_row">{{ supplierDue.total_due }}</strong>
                        <span v-else>{{ supplierDue.total_due }}</span>
                    </template>
                </Datatable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ref, onMounted, defineProps, computed } from "vue";
import Datatable from "@/Components/kt-datatable/KTDataTable.vue";
import type { Sort } from "@/Components/kt-datatable/table-partials/Models";
import arraySort from "array-sort";
import { Link } from "@inertiajs/vue3";
import KTIcon from "@/Core/helpers/kt-icon/KTIcon.vue";
import { MenuComponent } from "@/Assets/ts/components";
import i18n from '@/Core/plugins/i18n';
import {checkPermission} from "@/Core/helpers/Permission";
import axios from "axios";
import Swal from "sweetalert2";
import CompanyInformation from "@/Components/CompanyInformation.vue";

const { t } = i18n.global;

const props = defineProps({
    configuration: Object,
    supplierDues: Object as () => ISupplierDue[] | undefined,
    totalDue: Number,
    breadcrumbs: Array as () => Breadcrumb[],
    pageTitle: String,
});

interface Breadcrumb {
    url: string;
    title: string;
}

interface ISupplierDue {
    id: number;
    name: string;
    total_transaction: number;
    total_payment: number;
    total_due: number;
    mobile_number: string;
    is_total_row?: boolean;
}

const tableHeader = ref([
    { columnName: t('label.name'), columnLabel: "name", sortEnabled: true },
    { columnName: t('label.mobileNumber'), columnLabel: "mobile_number", sortEnabled: true },
    { columnName: t('buyer.label.totalTransaction'), columnLabel: "total_transaction", sortEnabled: true },
    { columnName: t('label.totalPayment'), columnLabel: "total_payment", sortEnabled: true },
    { columnName: t('label.totalDue'), columnLabel: "total_due", sortEnabled: true },
]);

const tableData = ref<ISupplierDue[]>([]);
const initSupplierDues = ref<ISupplierDue[]>([]);

onMounted(() => {
    if (props.supplierDues) {
        initSupplierDues.value = props.supplierDues;
        tableData.value = initSupplierDues.value;
    }
});

//Calculate Summation
const getTotal = (field: keyof ISupplierDue) => {
  return tableData.value.reduce((sum, row) => sum + Number(row[field] || 0), 0);
};

const computedSupplierDueTable = computed(() => {
    const rows = [...tableData.value];
    const totalTransaction = getTotal('total_transaction');
    const totalPayment = getTotal('total_payment');
    const totalDue = getTotal('total_due');

    rows.push({
        id: 0,
        name: '',
        mobile_number: '',
        is_total_row: true,
        total_transaction: totalTransaction,
        total_payment: totalPayment,
        total_due: totalDue,
    });

    return rows;
});

const search = ref<string>("");
const searchData = () => {
    tableData.value = [...initSupplierDues.value];
    if (search.value !== "") {
        tableData.value = tableData.value.filter((item) =>
            Object.values(item).some(value => value.toString().toLowerCase().includes(search.value.toLowerCase()))
        );
    }
    MenuComponent.reinitialization();
};

const sortData = (sort: Sort) => {
    const reverse: boolean = sort.order === "asc";
    if (sort.label) {
        arraySort(tableData.value, sort.label, { reverse });
    }
};

const sendReminder = async () => {
    const dueSuppliers = tableData.value.filter(supplier => supplier.total_due > 0); // Filter suppliers with due
    if (dueSuppliers.length === 0) {
        Swal.fire({
            text: `${t('confirmation.supplier.noSuppliers')}`,
            icon: "info",
            confirmButtonText: `${t('confirmation.gotIt')}`,
            heightAuto: false,
            buttonsStyling: false,
            customClass: {
                confirmButton: "btn fw-semibold btn-light-primary",
            },
        });
        return;
    }

    // Show confirmation dialog
    const result = await Swal.fire({
        text: `${t('confirmation.supplier.confirmReminder', { count: dueSuppliers.length })}`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: `${t('confirmation.supplier.yesSendSMS')}`,
        cancelButtonText: `${t('confirmation.cancel')}`,
        heightAuto: false,
        buttonsStyling: false,
        customClass: {
            confirmButton: "btn fw-semibold btn-danger",
            cancelButton: "btn fw-semibold btn-light",
        },
    });

    if (!result.isConfirmed) {
        return;
    }

    try {
        // Axios request to send reminders
        const response = await axios.post(route('suppliers.sendDueReminder'), {});

        // Handle successful response
        Swal.fire({
            text: `${t('confirmation.supplier.reminderSent')}`,
            icon: "success",
            confirmButtonText: `${t('confirmation.gotIt')}`,
            heightAuto: false,
            buttonsStyling: false,
            customClass: {
                confirmButton: "btn fw-semibold btn-light-primary",
            },
        });
    } catch (error) {
        // Handle error response
        console.error(error);
        alert("An error occurred while sending reminders.");
    }
};

const printInvoice = () => {
    document.title = 'Supplier Due Report';
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
