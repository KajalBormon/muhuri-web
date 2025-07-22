<template>
    <AuthenticatedLayout :breadcrumbs="props?.breadcrumbs" :pageTitle="props?.pageTitle">
        <div id="kt_content_container" class="d-flex flex-column-fluid align-items-start container-xxl">
            <div class="content flex-row-fluid" id="kt_content">
                <div class="d-flex flex-column flex-lg-row">
                    <!-- User Information Section -->
                    <div class="flex-column flex-lg-row-auto w-lg-250px w-xl-350px mb-10" id="no-print">
                        <div class="card mb-5 mb-xl-8">
                            <div class="card-body">
                                <!--begin::Summary-->
                                <div class="d-flex flex-center flex-column py-5">
                                    <!--begin::Avatar-->
                                    <div class="symbol symbol-50px symbol-circle mb-7">
                                        <!-- <img :src="getAssetPath('media/avatars/300-1.jpg')" alt="Emma Smith" class="w-100" /> -->
                                        <div class="symbol-label fs-3 bg-light-danger text-danger" >
                                            {{ getInitials(props?.supplier) }}
                                        </div>
                                    </div>
                                    <!--end::Avatar-->

                                    <!--begin::Name-->
                                    <a href="#" class="fs-3 text-gray-800 text-hover-primary fw-bold mb-3">{{ props.supplier?.name }}</a
                                    >
                                    <!--end::Name-->

                                    <!--begin::Mobile Number-->
                                    <div class="mb-2">
                                        <div class="me-3">
                                            {{ props.supplier?.mobile_number }}
                                        </div>
                                    </div>

                                    <div class="mb-9">
                                        <div class="me-3">
                                            {{ props.address }}
                                        </div>
                                    </div>
                                    <!--end::Mobile Number-->
                                </div>
                                <!--end::Summary-->

                                <div class="separator"></div>
                                <!--begin::Details content-->
                                <div id="kt_user_view_details" class="collapse show">
                                    <div class="pb-5 fs-6">
                                        <!--begin::Details item-->
                                        <div class="fw-bold mt-5">
                                            {{ $t('label.totalAmount') }}
                                        </div>
                                        <div class="text-gray-600">
                                            <a href="#" class="text-gray-600 text-hover-primary">
                                                {{ props.supplierSummary?.total_transaction }}
                                            </a>
                                        </div>

                                        <div class="fw-bold mt-5">
                                            {{ $t('label.totalPayment') }}
                                        </div>
                                        <div class="text-gray-600">
                                            <a href="#" class="text-gray-600 text-hover-primary">
                                                {{ props.supplierSummary?.total_payment }}
                                            </a>
                                        </div>

                                        <div class="fw-bold mt-5">
                                            {{ $t('label.totalDue') }}
                                        </div>
                                        <div class="text-gray-600">
                                            <a href="#" :style="props.supplierSummary?.total_due < 0 ? 'color: red' : 'color: gray'">
                                                {{ props.supplierSummary?.total_due }}
                                            </a>
                                        </div>
                                        <!--begin::Details item-->
                                    </div>
                                </div>
                                <!--end::Details content-->
                            </div>
                        </div>
                    </div>

                    <!-- Tab Sections -->
                    <div class="flex-lg-row-fluid ms-lg-15">
                        <!--begin::Nav Tab-->
                        <div class="card-toolbar m-0" id="no-print">
                            <ul class="nav nav-stretch fs-5 fw-semibold nav-line-tabs nav-line-tabs-2x border-transparent" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a
                                        id="kt_referrals_branch_details_tab"
                                        class="nav-link text-active-primary"
                                        data-bs-toggle="tab"
                                        role="tab"
                                        href="javascript:void(0)"
                                        @click="activeTab = 1"
                                        :class="{ active: activeTab === 1 }"
                                    >
                                        {{ $t('supplier.header.transactions') }}
                                    </a>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <a
                                        id="kt_referrals_employee_list_tab"
                                        class="nav-link text-active-primary"
                                        data-bs-toggle="tab"
                                        role="tab"
                                        href="javascript:void(0)"
                                        @click="activeTab = 2"
                                        :class="{ active: activeTab === 2 }"
                                    >
                                        {{ $t('supplier.header.payment') }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <!--end::Nav Tab-->

                        <!-- Start: My Sale Section -->
                        <div id="kt_referred_employees_tab_content" class="tab-content">
                            <div id="branch_details" class="py-4 tab-pane fade active show" role="tabpanel" v-show="activeTab === 1">
                                <div class="card card-flush mb-6 mb-xl-9">
                                    <div id="print-only">
                                        <CompanyInformation :configuration="props?.configuration" />
                                         <div id="invoice-content">
                                            <h5 class="fs-3 text-center">{{ $t('report.label.fromDate', { date: formData.from_date }) }} - {{ $t('report.label.toDate', { date: formData.to_date, title: t('buyer.label.transaction') }) }}</h5>
                                        </div>
                                    </div>
                                    <div class="card-header mt-6" id="no-print">
                                        <div class="card-title flex-column">
                                            <h2 class="mb-1">
                                                {{ $t('supplier.header.transactions') }}
                                            </h2>
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
                                                            <!-- Start: Sale type filter -->
                                                            <div class="mb-10">
                                                                <label class="form-label fs-6 fw-semibold">{{ $t('filterOptions.report.subTitle.label.saleType') }}:</label>
                                                                <Multiselect
                                                                    :placeholder = "$t('filterOptions.report.subTitle.placeholder.saleType')"
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
                                                <button type="button" class="btn btn-primary my-1 me-12" @click="printInvoice">{{ $t('sale.buttonValue.printInvoice') }}</button>
                                                <!--end::Print-->
                                            </div>
                                            <!--end::Toolbar-->
                                        </div>
                                    </div>
                                    <div class="card-body" id="invoice-content">
                                        <Datatable
                                            @on-sort="sortData"
                                            :data="computedPurchaseTable"
                                            :header="purchaseTableHeader"
                                            :enable-items-per-page-dropdown="false"
                                            :items-per-page="Infinity"
                                            :pagination-enabled="false"
                                            :checkbox-enabled="false"
                                        >
                                            <template v-slot:purchase_date="{ row: purchase }">
                                                <span v-if="!purchase.is_total_row">{{ formatDate(purchase.purchase_date) }}</span>
                                                <span v-else></span>
                                            </template>

                                            <template v-slot:invoice_number="{ row: purchase }">
                                                <span v-if="!purchase.is_total_row">{{ purchase.invoice_number }}</span>
                                                <strong v-else class="text-end">Total</strong>
                                            </template>

                                            <template v-slot:grand_total="{ row: purchase }">
                                                <strong v-if="purchase.is_total_row">{{ purchase.grand_total.toFixed(2) }}</strong>
                                                <span v-else>{{ purchase.grand_total.toFixed(2) }}</span>
                                            </template>

                                            <template v-slot:paid_amount="{ row: purchase }">
                                                <strong v-if="purchase.is_total_row">{{ purchase.paid_amount.toFixed(2) }}</strong>
                                                <span v-else>{{ purchase.paid_amount.toFixed(2) }}</span>
                                            </template>

                                            <template v-slot:due_amount="{ row: purchase }">
                                                <strong v-if="purchase.is_total_row">{{ purchase.due_amount.toFixed(2) }}</strong>
                                                <span v-else>{{ (purchase.grand_total - purchase.paid_amount).toFixed(2) }}</span>
                                            </template>

                                            <template v-slot:action="{ row: purchase }">
                                                <div class="no-print">
                                                    <Link
                                                        v-if="purchase?.id"
                                                        :href="route('purchases.show', purchase)"
                                                        class="btn btn-icon btn-flex btn-active-light-primary w-30px h-30px"
                                                        data-bs-toggle="tooltip"
                                                        :title="$t('tooltip.title.view')"
                                                    >
                                                        <KTIcon icon-name="book" icon-class="fs-3 text-primary" />
                                                    </Link>
                                                </div>
                                            </template>
                                        </Datatable>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End: My Sale Section -->

                        <!-- Start Payment -->
                        <div id="kt_referred_employees_tab_content" class="tab-content">
                            <div id="branch_details" class="py-4 tab-pane fade active show" role="tabpanel" v-show="activeTab === 2">
                                <div class="card card-flush mb-6 mb-xl-9">
                                  <div id="print-only">
                                        <CompanyInformation :configuration="props?.configuration" />
                                         <div id="invoice-content">
                                            <h5 class="fs-3 text-center">{{ $t('report.label.fromDate', { date: formData.from_date }) }} - {{ $t('report.label.toDate', { date: formData.to_date, title: t('buyer.header.payment') }) }}</h5>
                                        </div>
                                    </div>
                                    <div class="card-header mt-6" id="no-print">
                                        <div class="card-title">
                                            <div class="d-flex align-items-center position-relative my-1">
                                                <h2>{{ $t('supplier.header.payment') }}</h2>
                                            </div>
                                        </div>

                                        <div class="card-toolbar">
                                            <!--begin::Toolbar-->
                                            <div class="d-flex justify-content-end" data-kt-customer-table-toolbar="base">
                                                <!--begin::Add Supplier Payment-->
                                                <button
                                                    v-if="checkPermission('can-create-supplier-payment')"
                                                    type="button"
                                                    class="btn btn-primary my-1 me-3"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#kt_modal_add_supplier_payment"
                                                >
                                                    <KTIcon icon-name="plus" icon-class="fs-2"/>
                                                    {{ $t('supplier.header.show.buttonValue.addPayment') }}
                                                </button>
                                                <!--end::Add Supplier Payment -->
                                                <!-- Start: Filter -->
                                                <button type="button" class="btn btn-light-primary my-1 me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
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
                                                            <div class="d-flex justify-content-end">
                                                                <button type="reset" class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6" data-kt-menu-dismiss="true" data-kt-user-table-filter="reset" @click="applyReset()">{{ $t('buttonValue.reset') }}</button>
                                                                <button class="btn btn-primary fw-semibold px-6" data-kt-menu-dismiss="true" data-kt-user-table-filter="filter" @click="applyFilter()">{{ $t('buttonValue.apply') }}</button>
                                                            </div>
                                                            <!-- End: Date filter -->
                                                        </VForm>
                                                    </div>
                                                    <!--end::Content-->
                                                </div>
                                                <!--End: Filter-->
                                                <!--begin::Print-->
                                                <button type="button" class="btn btn-primary my-1 me-12" @click="printPaymentInvoice">{{ $t('sale.buttonValue.printInvoice') }}</button>
                                            </div>
                                            <!--end::Toolbar-->
                                        </div>
                                    </div>
                                    <div class="card-body" id="invoice-content">
                                        <Datatable
                                            @on-sort="sortData"
                                            :data="computedPaymentTable"
                                            :header="tableHeader"
                                            :enable-items-per-page-dropdown="false"
                                            :items-per-page="Infinity"
                                            :pagination-enabled="true"
                                            :checkbox-enabled="false"
                                        >
                                            <!-- Date -->
                                            <template v-slot:payment_date="{ row: supplierPayment }">
                                                <span v-if="!supplierPayment.is_total_amount">{{ formatDate(supplierPayment.payment_date) }}</span>
                                                <strong v-else class="text-end">{{ $t('label.total') }}</strong>
                                            </template>

                                            <template v-slot:payment="{ row: supplierPayment }">
                                                <strong v-if="supplierPayment.is_total_amount">
                                                    {{ supplierPayment.amount }}
                                                </strong>
                                                <span v-else>
                                                    {{ supplierPayment.amount }}
                                                    <div class="fs-6 fw-semibold text-muted">
                                                        <small>{{ supplierPayment.payment_method }}</small>
                                                    </div>
                                                </span>
                                            </template>

                                            <!-- Action -->
                                            <template v-slot:actions="{ row: supplierPayment }">
                                                <div class="d-flex align-items-center justify-content-end no-print">
                                                    <button
                                                        v-if="supplierPayment?.id"
                                                        type="button"
                                                        class="btn btn-icon btn-active-light-primary w-30px h-30px ms-auto"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#kt_modal_edit_supplier_payment"
                                                        @click="openEditModal(supplierPayment)"
                                                    >
                                                        <in class="ki-duotone ki-pencil fs-3">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                        </in>
                                                    </button>
                                                    <!-- Delete -->
                                                    <DeleteConfirmationButton v-if="checkPermission('can-delete-supplier-payment') && supplierPayment?.id" iconClass="fs-1" :supplierId="props?.supplier?.id" :payment="supplierPayment" confirmRoute="suppliers.payments.destroy" title="Supplier Payment" :messageTitle="`${supplierPayment?.payment_date} তারিখের ${supplierPayment?.amount} টাকার পেমেন্ট?`"/>

                                                    <Link  v-if="supplierPayment?.id" :href="route('suppliers.payments.show', [props.supplier?.id, supplierPayment?.id])" class="btn btn-icon btn-flex btn-active-light-primary w-30px h-30px" data-bs-toggle="tooltip" :title="$t('tooltip.title.view')">
                                                        <KTIcon icon-name="book" icon-class="fs-3 text-primary"/>
                                                    </Link>
                                                </div>
                                            </template>
                                        </Datatable>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Payment -->
                    </div>
                </div>
            </div>
        </div>

        <AddPayment
            :allPaymentMethod="props?.paymentMethods"
            :supplier="props?.supplier"
            :totalDue="props.supplierSummary?.total_due"
        ></AddPayment>
        <EditPayment
            :allPaymentMethod="props?.paymentMethods"
            :supplier="props?.supplier"
            :payment="selectedSupplierPayment"
            :totalDue="props.supplierSummary?.total_due"
        ></EditPayment>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ref, onMounted, onBeforeMount, watch, computed } from "vue";
import { usePage, Link, useForm } from "@inertiajs/vue3";
import { Field } from "vee-validate";
import Multiselect from '@vueform/multiselect';
import { Inertia } from '@inertiajs/inertia';
import { checkPermission } from "@/Core/helpers/Permission";
import arraySort from "array-sort";
import KTIcon from "@/Core/helpers/kt-icon/KTIcon.vue";
import Datatable from "@/Components/kt-datatable/KTDataTable.vue";
import type { Sort } from "@/Components/kt-datatable/table-partials/Models";
import { getInitials } from "@/Core/helpers/Helper";
import i18n from "@/Core/plugins/i18n";
import dayjs from "dayjs";
import AddPayment from "@/Pages/Supplier/Modal/AddPayment.vue";
import DeleteConfirmationButton from "@/Components/Button/DeleteConfirmationButton.vue";
import EditPayment from "@/Pages/Supplier/Modal/EditPayment.vue";
import CompanyInformation from "@/Components/CompanyInformation.vue";

const { t } = i18n.global;

const props = defineProps({
    supplier: Object,
    address: String,
    paymentMethods: Array,
    supplierPayments: Array as () => ISupplierPayment[],
    supplierSummary: Object,
    purchases: Object as () => IPurchase[] | undefined,
    purchaseTypes: Array,
    query: Object,
    configuration: Object,
    breadcrumbs: Array as () => Breadcrumb[],
    pageTitle: String,
});

interface Breadcrumb {
    url: string;
    title: string;
}

interface ISupplierPayment {
    id: number;
    payment_date: string;
    payment_method: string;
    invoice_number: number;
    amount: number;
    note: string;
    is_total_amount?: boolean;
}

interface IPurchase {
    id: number;
    purchase_date: string;
    invoice_number: number;
    grand_total: number;
    paid_amount: number;
    due_amount?: number;
    is_total_row?: boolean;
}

const activeTab = ref(1);
const tabTitle = ref(props?.pageTitle);

watch(activeTab, (newValue) => {
    if (activeTab.value == 1) {
        tabTitle.value = "Transactions";
    } else {
        tabTitle.value = "Payments";
    }
});

// If a user status is changed, the following code be will be able to keep in the same tab.
onBeforeMount(() => {
    const flash = usePage().props.flash;
    let { success, error } = flash as any;

    if (flash && (success || error)) {
        activeTab.value = 2;
    }
});

const tableHeader = ref([
    {
        columnName: t('label.date'),
        columnLabel: "payment_date",
        sortEnabled: true,
        columnWidth: 150,
    },
    {
        columnName: t('supplier.header.show.payment'),
        columnLabel: "payment",
        sortEnabled: true,
        columnWidth: 100,
    },
    {
        columnName: t('label.actions'),
        columnLabel: "actions",
        sortEnabled: true,
        columnWidth: 100,
    }
]);

const tableData = ref<ISupplierPayment[]>([]);
const initSupplierPayments = ref<ISupplierPayment[]>([]);

onMounted(() => {
    if (props.supplierPayments) {
        initSupplierPayments.value = props.supplierPayments.map(
            (supplierPayment: any) => ({
                id: supplierPayment.id,
                payment_date: supplierPayment.payment_date,
                payment_method: supplierPayment.payment_method,
                invoice_number: supplierPayment.invoice_number,
                amount: supplierPayment.amount,
                note: supplierPayment.note,
            }),
        );

        tableData.value = [...initSupplierPayments.value];
    }
});

const getTotalAmount = (field: keyof ISupplierPayment) => {
  return tableData.value.reduce((sum, row) => sum + Number(row[field] || 0), 0);
};
const computedPaymentTable = computed(() => {
    const rows = [...tableData.value];
    const totalAmount = getTotalAmount('amount');
    rows.push({
        id: 0,
        payment_date: '',
        invoice_number: 0,
        payment_method: '',
        note: '',
        is_total_amount: true,
        amount: totalAmount,
    });
    return rows;
});

const purchaseTableHeader = ref([
    {
        columnName: t('purchase.label.purchaseDate'),
        columnLabel: "purchase_date",
        sortEnabled: true,
        columnWidth: 120,
    },
    {
        columnName: t('sale.label.invoiceNumber'),
        columnLabel: "invoice_number",
        sortEnabled: true,
        columnWidth: 120,
    },
    {
        columnName: t('sale.label.totalAmount'),
        columnLabel: "grand_total",
        sortEnabled: true,
        columnWidth: 100,
    },
    {
        columnName: t('sale.label.paid'),
        columnLabel: "paid_amount",
        sortEnabled: true,
        columnWidth: 100,
    },
    {
        columnName: t('sale.label.dueAmount'),
        columnLabel: "due_amount",
        sortEnabled: true,
        columnWidth: 80,
    },
    {
        columnName: t('label.actions'),
        columnLabel: "action",
        sortEnabled: true,
        columnWidth: 100,
    }
]);

const purchaseTableData = ref<IPurchase[]>([]);
const initPurchases = ref<IPurchase[]>([]);

onMounted( () => {
    if(props.purchases){
        initPurchases.value = props.purchases.map((purchase: any)=>({
            id: purchase.id,
            purchase_date: purchase.purchase_date,
            invoice_number: purchase.invoice_number,
            paid_amount: purchase.paid_amount,
            grand_total: purchase.grand_total,
        }));
        purchaseTableData.value = initPurchases.value;
    }
});

const formatDate = (dateString: string) => {
    return dayjs(dateString).format("DD MMMM YYYY");
};

const selectedSupplierPayment = ref({});
const openEditModal = (supplierPayment: any) => {
    selectedSupplierPayment.value = supplierPayment;
};

const sortData = (sort: Sort) => {
    const reverse: boolean = sort.order === "asc";
    if (sort.label) {
        arraySort(tableData.value, sort.label, {
            reverse,
        });
    }
};

const getTotal = (field: keyof IPurchase) => {
  return purchaseTableData.value.reduce((sum, row) => sum + Number(row[field] || 0), 0);
};

const computedPurchaseTable = computed(() => {
    const rows = [...purchaseTableData.value];
    const totalGrand = getTotal('grand_total');
    const totalPaid = getTotal('paid_amount');
    const totalDue = totalGrand - totalPaid;
    rows.push({
        id: 0,
        purchase_date: '',
        invoice_number: 0,
        is_total_row: true,
        grand_total: totalGrand,
        paid_amount: totalPaid,
        due_amount: totalDue
    });
    return rows;
});

const today = dayjs().format('YYYY-MM-DD');
const { from_date, to_date } = props.query || { from_date: '', to_date: '' };

const formData = useForm({
    from_date: from_date || today,
    to_date: to_date || today,
    purchase_type: ''
});

// Apply Filter Function
const applyFilter = () => {
    Inertia.replace(route('suppliers.show', props?.supplier?.id), {
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

const printInvoice = () => {
    document.title = `${props?.supplier?.name} Total Transaction Report `;
    window.print();
};

const printPaymentInvoice = () => {
    document.title = `${props?.supplier?.name} Payment Report `;
    window.print();
};
</script>

<style>
    #print-only{
        display: none;
    }
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
        #no-print {
            display: none !important;
        }
        .no-print {
            display: none !important;
        }
        #print-only{
            display: block;
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
        table th:last-child,
        table td:last-child {
            display: none !important;
        }
    }
</style>
