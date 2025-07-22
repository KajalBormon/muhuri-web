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
                            :placeholder="$t('buyer.placeholder.searchBuyers')"
                        />
                    </div>
                </div>
                <div class="card-toolbar">
                    <!--begin::Toolbar-->
                    <div class="d-flex justify-content-end" data-kt-customer-table-toolbar="base" >
                        <!--begin::Add Fiscal Year-->
                        <button v-if="checkPermission ('can-create-buyer')"
                            class="btn btn-primary me-3"
                            @click.prevent="sendReminder"
                        >
                            <i class="fa-solid fa-bell"></i> {{ $t("buyer.header.sendDueReminder") }}
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
                    :data="computedBuyerDueTable"
                    :header="tableHeader"
                    :enable-items-per-page-dropdown="false"
                    :items-per-page="Infinity"
                    :pagination-enabled="true"
                    :checkbox-enabled="false"
                >
                    <template v-slot:name="{ row: buyerDue }">
                        <Link :href="route('buyers.show', buyerDue.id)" class="text-gray-800 text-hover-primary">
                            {{ buyerDue.name }}
                        </Link>
                    </template>

                    <template v-slot:mobile_number="{ row: buyerDue }">
                        <span v-if="!buyerDue.is_total_row">{{ buyerDue.mobile_number }}</span>
                        <strong v-else class="text-end">{{ $t('label.total') }}</strong>
                    </template>

                    <template v-slot:total_transaction="{ row: buyerDue }">
                        <strong v-if="buyerDue.is_total_row">{{ buyerDue.total_transaction }}</strong>
                        <span v-else>{{ buyerDue.total_transaction }}</span>
                    </template>

                    <template v-slot:total_payment="{ row: buyerDue }">
                        <strong v-if="buyerDue.is_total_row">{{ buyerDue.total_payment }}</strong>
                        <span v-else>{{ buyerDue.total_payment }}</span>
                    </template>

                    <template v-slot:total_due="{ row: buyerDue }">
                        <strong v-if="buyerDue.is_total_row">{{ buyerDue.total_due }}</strong>
                        <span v-else>{{ buyerDue.total_due }}</span>
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
    buyerDues: Object as () => IBuyerDue[] | undefined,
    totalDue: Number,
    breadcrumbs: Array as () => Breadcrumb[],
    pageTitle: String,
});

interface Breadcrumb {
    url: string;
    title: string;
}

interface IBuyerDue {
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

const tableData = ref<IBuyerDue[]>([]);
const initBuyerDues = ref<IBuyerDue[]>([]);

onMounted(() => {
    if (props.buyerDues) {
        initBuyerDues.value = props.buyerDues;
        tableData.value = initBuyerDues.value;
    }
});

//Calculate Summation
const getTotal = (field: keyof IBuyerDue) => {
  return tableData.value.reduce((sum, row) => sum + Number(row[field] || 0), 0);
};

const computedBuyerDueTable = computed(() => {
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
    tableData.value = [...initBuyerDues.value];
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
    const dueBuyers = tableData.value.filter(buyer => buyer.total_due > 0); // Filter buyers with due
    if (dueBuyers.length === 0) {
        Swal.fire({
            text: `${t('confirmation.buyer.noBuyers')}`,
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
        text: `${t('confirmation.buyer.confirmReminder', { count: dueBuyers.length })}`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: `${t('confirmation.buyer.yesSendSMS')}`,
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
        const response = await axios.post(route('buyers.sendDueReminder'), {});

        // Handle successful response
        Swal.fire({
            text: `${t('confirmation.buyer.reminderSent')}`,
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
    document.title = 'Buyer Due Report';
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
